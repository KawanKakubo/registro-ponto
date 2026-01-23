<?php

namespace App\Services;

use App\Models\EmployeeRegistration;
use App\Models\TimeRecord;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TimesheetGeneratorService
{
    protected WorkShiftAssignmentService $assignmentService;
    protected RotatingShiftCalculationService $rotatingService;
    protected FlexibleHoursCalculationService $flexibleService;

    public function __construct()
    {
        $this->assignmentService = app(WorkShiftAssignmentService::class);
        $this->rotatingService = app(RotatingShiftCalculationService::class);
        $this->flexibleService = app(FlexibleHoursCalculationService::class);
    }

    /**
     * Gera cartão de ponto para um vínculo (matrícula) específico
     * 
     * @param EmployeeRegistration $registration Vínculo (matrícula) do colaborador
     * @param string $startDate Data inicial (Y-m-d)
     * @param string $endDate Data final (Y-m-d)
     * @return array Dados do cartão de ponto
     */
    public function generate(EmployeeRegistration $registration, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Buscar registros de ponto deste vínculo específico
        $timeRecords = TimeRecord::where('employee_registration_id', $registration->id)
            ->whereBetween('record_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(function($record) {
                return Carbon::parse($record->record_date)->format('Y-m-d');
            });

        $period = CarbonPeriod::create($start, $end);
        
        $dailyRecords = [];
        $calculations = [];
        
        // Verificar tipo de jornada DESTE vínculo
        $currentAssignment = $registration->currentWorkShiftAssignment;
        $isFlexibleHours = $currentAssignment && $currentAssignment->template->type === 'weekly_hours';
        $isRotatingShift = $currentAssignment && $currentAssignment->template->type === 'rotating_shift';
        
        if ($isFlexibleHours && $currentAssignment->template->flexibleHours) {
            // Para jornadas flexíveis, calcular por período
            $flexibleBalance = $this->flexibleService->calculatePeriodBalance(
                $registration,
                $start,
                $end,
                $currentAssignment->template->flexibleHours,
                $timeRecords
            );
            
            // Preencher cálculos diários baseado no balanço do período
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $records = $timeRecords[$dateStr] ?? collect();
                $dailyRecords[$dateStr] = $records;
                
                // Para horas flexíveis, mostrar apenas horas trabalhadas diariamente
                $workedMinutes = $records->isNotEmpty() ? $this->calculateWorkedMinutes($records) : 0;
                
                $calculations[$dateStr] = [
                    'worked' => $workedMinutes,
                    'expected' => 0, // Não há expectativa diária fixa
                    'overtime' => 0, // Calculado no período, não diariamente
                    'absence' => 0,  // Calculado no período, não diariamente
                    'is_flexible' => true,
                ];
            }
            
            // Adicionar resumo do período ao retorno
            return [
                'registration' => $registration,
                'person' => $registration->person,
                'establishment' => $registration->establishment,
                'startDate' => $start->format('Y-m-d'),
                'endDate' => $end->format('Y-m-d'),
                'dailyRecords' => $dailyRecords,
                'calculations' => $calculations,
                'flexible_summary' => $flexibleBalance,
                'is_flexible_hours' => true,
                'is_rotating_shift' => false,
                'rotating_summary' => null,
            ];
        }

        // Para jornadas fixas e de revezamento (comportamento original)
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            
            $records = $timeRecords[$dateStr] ?? collect();
            $dailyRecords[$dateStr] = $records;
            
            // Usa WorkShiftAssignmentService para obter horário esperado DESTE vínculo
            $expectedSchedule = $this->assignmentService->getEmployeeScheduleForDate($registration->id, $dateStr);
            
            $calculations[$dateStr] = $this->calculateHours(
                $records,
                $expectedSchedule,
                $date
            );
        }

        // Se for jornada de revezamento, calcular resumo do ciclo
        $rotatingSummary = null;
        if ($isRotatingShift && $currentAssignment->template->rotatingRule) {
            $rotatingSummary = $this->calculateRotatingSummary(
                $calculations,
                $currentAssignment->template->rotatingRule
            );
        }

        return [
            'registration' => $registration,
            'person' => $registration->person,
            'establishment' => $registration->establishment,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
            'dailyRecords' => $dailyRecords,
            'calculations' => $calculations,
            'is_flexible_hours' => false,
            'is_rotating_shift' => $isRotatingShift,
            'rotating_summary' => $rotatingSummary,
        ];
    }

    protected function calculateHours($records, $expectedSchedule, Carbon $date): array
    {
        if ($records->isEmpty()) {
            if ($expectedSchedule && isset($expectedSchedule['is_work_day']) && $expectedSchedule['is_work_day']) {
                $expectedMinutes = $this->calculateExpectedMinutesFromArray($expectedSchedule);
                return [
                    'worked' => 0,
                    'expected' => $expectedMinutes,
                    'overtime' => 0,
                    'absence' => $expectedMinutes,
                    'inconsistency' => false,
                    'incomplete_last' => null,
                ];
            }
            
            return [
                'worked' => 0,
                'expected' => 0,
                'overtime' => 0,
                'absence' => 0,
            ];
        }

    // Obter detalhes das batidas (inclui detecção de ímpar/inconsistência)
    $workedDetail = $this->calculateWorkedMinutesDetailed($records);
    $workedMinutes = $workedDetail['worked'];
    $inconsistency = $workedDetail['incomplete'];
    $incompleteLast = $workedDetail['last_unpaired_time'];
        
        if (!$expectedSchedule) {
            return [
                'worked' => $workedMinutes,
                'expected' => 0,
                'overtime' => 0,
                'absence' => 0,
            ];
        }

        $expectedMinutes = $this->calculateExpectedMinutesFromArray($expectedSchedule);
        $difference = $workedMinutes - $expectedMinutes;
        
        return [
            'worked' => $workedMinutes,
            'expected' => $expectedMinutes,
            'overtime' => $difference > 0 ? $difference : 0,
            'absence' => $difference < 0 ? abs($difference) : 0,
            // Indica se há batida ímpar/inconsistente neste dia
            'inconsistency' => $inconsistency,
            'incomplete_last' => $incompleteLast,
        ];
    }

    protected function calculateWorkedMinutes($records): int
    {
        $punches = $records->sortBy('recorded_at')->pluck('recorded_at')->toArray();
        $totalMinutes = 0;
        
        // Calcula em pares (entrada/saída)
        for ($i = 0; $i < count($punches) - 1; $i += 2) {
            if (isset($punches[$i + 1])) {
                $entry = Carbon::parse($punches[$i]);
                $exit = Carbon::parse($punches[$i + 1]);
                
                // Calcula a diferença corretamente (exit - entry)
                $totalMinutes += $entry->diffInMinutes($exit, false);
            }
        }
        
        return $totalMinutes;
    }

    /**
     * Retorna detalhes do cálculo de minutos trabalhados incluindo
     * indicação de batida ímpar (inconsistência) e horário da última batida não pareada
     *
     * @param \Illuminate\Support\Collection|array $records
     * @return array [worked => int(mins), incomplete => bool, last_unpaired_time => string|null]
     */
    protected function calculateWorkedMinutesDetailed($records): array
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

        $count = count($punches);
        $incomplete = ($count % 2) !== 0;
        $lastUnpaired = null;
        if ($incomplete && $count > 0) {
            $lastUnpaired = Carbon::parse($punches[$count - 1])->format('H:i');
        }

        return [
            'worked' => $totalMinutes,
            'incomplete' => $incomplete,
            'last_unpaired_time' => $lastUnpaired,
        ];
    }

    protected function calculateExpectedMinutes($schedule): int
    {
        $totalMinutes = 0;
        
        if ($schedule->entry_1 && $schedule->exit_1) {
            $totalMinutes += $this->minutesBetween($schedule->entry_1, $schedule->exit_1);
        }
        
        if ($schedule->entry_2 && $schedule->exit_2) {
            $totalMinutes += $this->minutesBetween($schedule->entry_2, $schedule->exit_2);
        }
        
        if ($schedule->entry_3 && $schedule->exit_3) {
            $totalMinutes += $this->minutesBetween($schedule->entry_3, $schedule->exit_3);
        }
        
        return $totalMinutes;
    }

    protected function calculateExpectedMinutesFromArray(array $schedule): int
    {
        // Se o schedule tem daily_hours, converte para minutos
        if (isset($schedule['daily_hours'])) {
            return (int) round($schedule['daily_hours'] * 60);
        }

        $totalMinutes = 0;
        
        if (!empty($schedule['entry_1']) && !empty($schedule['exit_1'])) {
            $totalMinutes += $this->minutesBetween($schedule['entry_1'], $schedule['exit_1']);
        }
        
        if (!empty($schedule['entry_2']) && !empty($schedule['exit_2'])) {
            $totalMinutes += $this->minutesBetween($schedule['entry_2'], $schedule['exit_2']);
        }
        
        if (!empty($schedule['entry_3']) && !empty($schedule['exit_3'])) {
            $totalMinutes += $this->minutesBetween($schedule['entry_3'], $schedule['exit_3']);
        }
        
        return $totalMinutes;
    }

    protected function minutesBetween($time1, $time2): int
    {
        // Converte para string se for objeto Carbon/DateTime
        if (is_object($time1)) {
            $time1 = $time1 instanceof Carbon ? $time1->format('H:i:s') : (string) $time1;
        }
        if (is_object($time2)) {
            $time2 = $time2 instanceof Carbon ? $time2->format('H:i:s') : (string) $time2;
        }
        
        // Parse das strings de horário
        $t1 = Carbon::parse($time1);
        $t2 = Carbon::parse($time2);
        
        return $t2->diffInMinutes($t1);
    }

    protected function isWorkDay($schedule): bool
    {
        // Se é um array (novo formato)
        if (is_array($schedule)) {
            return isset($schedule['is_work_day']) && $schedule['is_work_day'];
        }

        // Se é um objeto (formato antigo)
        return $schedule->entry_1 !== null || $schedule->entry_2 !== null || $schedule->entry_3 !== null;
    }

    protected function calculateRotatingSummary(array $calculations, $rotatingRule): array
    {
        $workDaysCount = 0;
        $restDaysCount = 0;
        $totalWorkedMinutes = 0;
        $totalExpectedMinutes = 0;
        $totalOvertimeMinutes = 0;
        $totalAbsenceMinutes = 0;
        $daysWithPresence = 0;
        $daysWithAbsence = 0;

        foreach ($calculations as $calc) {
            if ($calc['expected'] > 0) {
                // Era dia de trabalho no ciclo
                $workDaysCount++;
                $totalExpectedMinutes += $calc['expected'];
                $totalWorkedMinutes += $calc['worked'];
                $totalOvertimeMinutes += $calc['overtime'];
                $totalAbsenceMinutes += $calc['absence'];

                if ($calc['worked'] > 0) {
                    $daysWithPresence++;
                }
                if ($calc['absence'] > 0) {
                    $daysWithAbsence++;
                }
            } else {
                // Era dia de folga
                $restDaysCount++;
            }
        }

        return [
            'cycle_info' => [
                'work_days' => $rotatingRule->work_days,
                'rest_days' => $rotatingRule->rest_days,
                'cycle_name' => $rotatingRule->work_days . 'x' . $rotatingRule->rest_days,
                'shift_duration' => $rotatingRule->shift_duration_hours,
            ],
            'period_stats' => [
                'work_days_in_period' => $workDaysCount,
                'rest_days_in_period' => $restDaysCount,
                'total_days' => $workDaysCount + $restDaysCount,
                'days_with_presence' => $daysWithPresence,
                'days_with_absence' => $daysWithAbsence,
            ],
            'hours' => [
                'expected_total' => $totalExpectedMinutes,
                'worked_total' => $totalWorkedMinutes,
                'overtime_total' => $totalOvertimeMinutes,
                'absence_total' => $totalAbsenceMinutes,
            ],
        ];
    }
}
