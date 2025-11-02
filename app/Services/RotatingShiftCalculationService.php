<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TemplateRotatingRule;
use App\Models\Attendance;
use Carbon\Carbon;
use Exception;

class RotatingShiftCalculationService
{
    /**
     * Calcula se o colaborador deveria trabalhar em uma data específica
     * baseado no ciclo de revezamento
     */
    public function shouldWorkOnDate(
        Employee $employee,
        Carbon $date,
        TemplateRotatingRule $rule
    ): bool {
        // 1. Pegar a data de início do ciclo do colaborador
        $assignment = $employee->workShiftAssignments()
            ->where('effective_from', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', $date);
            })
            ->first();
        
        if (!$assignment || !$assignment->cycle_start_date) {
            throw new Exception('Colaborador sem data de início de ciclo');
        }
        
        $cycleStartDate = Carbon::parse($assignment->cycle_start_date);
        
        // 2. Calcular quantos dias se passaram desde o início do ciclo
        $daysSinceStart = $cycleStartDate->diffInDays($date);
        
        // 3. Determinar a posição no ciclo atual
        $totalCycleDays = $rule->work_days + $rule->rest_days;
        $positionInCycle = $daysSinceStart % $totalCycleDays;
        
        // 4. Verificar se está em dia de trabalho
        $isWorkDay = $positionInCycle < $rule->work_days;
        
        return $isWorkDay;
    }
    
    /**
     * Valida as batidas de ponto para escala rotativa
     */
    public function validateAttendance(
        Employee $employee,
        Carbon $date,
        ?Attendance $attendance,
        TemplateRotatingRule $rule
    ): array {
        // Verificar se deveria trabalhar neste dia
        try {
            $shouldWork = $this->shouldWorkOnDate($employee, $date, $rule);
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'should_work' => null,
                'status' => 'error',
            ];
        }
        
        if (!$shouldWork) {
            // Não era dia de trabalho
            return [
                'should_work' => false,
                'status' => 'rest_day',
                'hours_worked' => 0,
                'hours_expected' => 0,
                'message' => 'Dia de folga no ciclo',
            ];
        }
        
        // Era dia de trabalho, validar horários
        if (!$attendance) {
            // Falta
            return [
                'should_work' => true,
                'status' => 'absent',
                'hours_worked' => 0,
                'hours_expected' => $rule->shift_duration_hours,
                'hours_missing' => $rule->shift_duration_hours,
                'message' => 'Ausência não justificada',
            ];
        }
        
        // Calcular horas trabalhadas
        $hoursWorked = $this->calculateHoursFromAttendance($attendance);
        $expectedHours = $rule->shift_duration_hours;
        
        // Verificar se cumpriu a jornada
        $toleranceHours = $rule->tolerance_minutes / 60;
        $difference = $expectedHours - $hoursWorked;
        
        if (!$rule->validate_exact_hours) {
            // Apenas verifica presença
            $status = $hoursWorked > 0 ? 'present' : 'absent';
        } else {
            // Valida horas exatas
            if (abs($difference) <= $toleranceHours) {
                $status = 'complete';
            } elseif ($hoursWorked < $expectedHours) {
                $status = 'incomplete';
            } else {
                $status = 'overtime';
            }
        }
        
        return [
            'should_work' => true,
            'status' => $status,
            'hours_worked' => round($hoursWorked, 2),
            'hours_expected' => round($expectedHours, 2),
            'difference' => round($difference, 2),
            'shift_start' => $rule->shift_start_time,
            'shift_end' => $rule->shift_end_time,
            'tolerance_minutes' => $rule->tolerance_minutes,
        ];
    }
    
    /**
     * Gera o calendário de trabalho para um período
     */
    public function generateWorkCalendar(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
        TemplateRotatingRule $rule
    ): array {
        $calendar = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            try {
                $shouldWork = $this->shouldWorkOnDate($employee, $currentDate, $rule);
                $calendar[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day_name' => $currentDate->dayName,
                    'should_work' => $shouldWork,
                    'type' => $shouldWork ? 'work' : 'rest',
                ];
            } catch (Exception $e) {
                $calendar[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day_name' => $currentDate->dayName,
                    'should_work' => null,
                    'type' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
            
            $currentDate->addDay();
        }
        
        return $calendar;
    }
    
    /**
     * Calcula horas trabalhadas de uma batida de ponto
     */
    private function calculateHoursFromAttendance(Attendance $attendance): float
    {
        $totalMinutes = 0;
        
        // Somar todos os períodos
        if ($attendance->entry_1 && $attendance->exit_1) {
            $totalMinutes += $this->minutesBetween($attendance->entry_1, $attendance->exit_1);
        }
        
        if ($attendance->entry_2 && $attendance->exit_2) {
            $totalMinutes += $this->minutesBetween($attendance->entry_2, $attendance->exit_2);
        }
        
        if ($attendance->entry_3 && $attendance->exit_3) {
            $totalMinutes += $this->minutesBetween($attendance->entry_3, $attendance->exit_3);
        }
        
        if ($attendance->entry_4 && $attendance->exit_4) {
            $totalMinutes += $this->minutesBetween($attendance->entry_4, $attendance->exit_4);
        }
        
        return $totalMinutes / 60;
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
}
