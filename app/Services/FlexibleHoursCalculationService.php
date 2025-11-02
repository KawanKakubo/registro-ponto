<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TemplateFlexibleHours;
use Carbon\Carbon;

class FlexibleHoursCalculationService
{
    /**
     * Calcula as horas trabalhadas em um período
     * e compara com a carga horária devida
     */
    public function calculatePeriodBalance(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
        TemplateFlexibleHours $config,
        $groupedTimeRecords = null
    ): array {
        // 1. Usar registros já agrupados ou buscar do banco
        if ($groupedTimeRecords === null) {
            $timeRecords = \App\Models\TimeRecord::where('employee_id', $employee->id)
                ->whereBetween('record_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('recorded_at')
                ->get()
                ->groupBy('record_date');
        } else {
            $timeRecords = $groupedTimeRecords;
        }
        
        // 2. Calcular total de horas trabalhadas
        $totalHoursWorked = 0;
        $workingDays = [];
        $dailyBreakdown = [];
        
        foreach ($timeRecords as $date => $records) {
            $dailyMinutes = $this->calculateDailyMinutesFromRecords($records);
            $dailyHours = $dailyMinutes / 60;
            
            // Verificar tolerância mínima
            $graceHours = $config->grace_minutes / 60;
            if ($dailyHours >= $graceHours) {
                $totalHoursWorked += $dailyHours;
                $workingDays[] = $date;
                
                $dailyBreakdown[] = [
                    'date' => $date,
                    'hours' => round($dailyHours, 2),
                    'counted' => true,
                ];
            } else {
                $dailyBreakdown[] = [
                    'date' => $date,
                    'hours' => round($dailyHours, 2),
                    'counted' => false,
                    'reason' => 'Abaixo da tolerância mínima',
                ];
            }
        }
        
        // 3. Calcular horas devidas no período
        $hoursRequired = $this->calculateRequiredHours(
            $config->period_type, 
            $config->weekly_hours_required,
            $startDate,
            $endDate
        );
        
        // 4. Calcular diferença
        $balance = $totalHoursWorked - $hoursRequired;
        
        // 5. Determinar status
        $tolerance = $config->grace_minutes / 60;
        
        if (abs($balance) <= $tolerance) {
            $status = 'complete';
            $statusText = 'Carga horária cumprida';
        } elseif ($balance < 0) {
            $status = 'insufficient';
            $statusText = 'Horas insuficientes';
        } else {
            $status = 'overtime';
            $statusText = 'Horas excedentes';
        }
        
        // 6. Validar regras opcionais
        $violations = [];
        
        if ($config->requires_minimum_daily_hours) {
            $dailyViolations = $this->validateMinimumDailyHours(
                $dailyBreakdown,
                $config->minimum_daily_hours
            );
            $violations = array_merge($violations, $dailyViolations);
        }
        
        if ($config->minimum_days_per_week) {
            $daysWorked = count($workingDays);
            $daysRequired = $config->minimum_days_per_week;
            
            if ($daysWorked < $daysRequired) {
                $violations[] = [
                    'type' => 'minimum_days',
                    'message' => "Trabalhou apenas {$daysWorked} dia(s), mínimo exigido: {$daysRequired}",
                ];
            }
        }
        
        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'type' => $config->period_type,
            ],
            'hours' => [
                'required' => round($hoursRequired, 2),
                'worked' => round($totalHoursWorked, 2),
                'balance' => round($balance, 2),
            ],
            'days' => [
                'worked' => count($workingDays),
                'dates' => $workingDays,
            ],
            'status' => $status,
            'status_text' => $statusText,
            'violations' => $violations,
            'daily_breakdown' => $dailyBreakdown,
        ];
    }
    
    /**
     * Calcula horas devidas baseado no tipo de período
     */
    private function calculateRequiredHours(
        string $periodType,
        float $weeklyHours,
        Carbon $start,
        Carbon $end
    ): float {
        // Calcular o número de semanas completas no período
        $totalDays = $start->diffInDays($end) + 1; // +1 para incluir o último dia
        $weeks = $totalDays / 7;
        
        switch ($periodType) {
            case 'weekly':
                // Para semanal, calcular proporcionalmente pelo número de semanas
                return $weeklyHours * $weeks;
            
            case 'biweekly':
                // Para quinzenal, usar período de 2 semanas como base
                return $weeklyHours * $weeks;
            
            case 'monthly':
                // Para mensal, calcular proporcionalmente
                return $weeklyHours * $weeks;
            
            default:
                return $weeklyHours * $weeks;
        }
    }
    
    /**
     * Calcula minutos trabalhados em um dia a partir dos registros de ponto
     */
    private function calculateDailyMinutesFromRecords($records): int
    {
        $punches = $records->sortBy('recorded_at')->pluck('recorded_at')->toArray();
        $totalMinutes = 0;
        
        // Calcula em pares (entrada/saída)
        for ($i = 0; $i < count($punches) - 1; $i += 2) {
            if (isset($punches[$i + 1])) {
                $entry = Carbon::parse($punches[$i]);
                $exit = Carbon::parse($punches[$i + 1]);
                
                $totalMinutes += $entry->diffInMinutes($exit, false);
            }
        }
        
        return $totalMinutes;
    }
    
    /**
     * Calcula minutos entre dois horários
     */
    private function minutesBetween($start, $end): int
    {
        $startTime = Carbon::parse($start);
        $endTime = Carbon::parse($end);
        
        // Se o fim é antes do início, assumir que cruzou a meia-noite
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }
        
        return $startTime->diffInMinutes($endTime);
    }
    
    /**
     * Valida se dias trabalhados cumprem mínimo de horas diárias
     */
    private function validateMinimumDailyHours($dailyBreakdown, float $minimumHours): array
    {
        $violations = [];
        
        foreach ($dailyBreakdown as $day) {
            $dailyHours = $day['hours'];
            
            if ($dailyHours > 0 && $dailyHours < $minimumHours) {
                $violations[] = [
                    'type' => 'minimum_daily_hours',
                    'date' => $day['date'],
                    'hours_worked' => round($dailyHours, 2),
                    'minimum_required' => $minimumHours,
                    'message' => "Dia {$day['date']}: trabalhou apenas " . round($dailyHours, 2) . "h, mínimo exigido: {$minimumHours}h",
                ];
            }
        }
        
        return $violations;
    }
}
