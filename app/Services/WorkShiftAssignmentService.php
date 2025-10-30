<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\WorkShiftTemplate;
use App\Models\EmployeeWorkShiftAssignment;
use App\Models\WorkSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class WorkShiftAssignmentService
{
    protected RotatingShiftCalculatorService $calculator;

    public function __construct(RotatingShiftCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Atribui um template de jornada para múltiplos colaboradores
     *
     * @param int $templateId ID do template
     * @param array $employeeIds IDs dos colaboradores
     * @param array $dates Datas de vigência e ciclo
     * @return array Resultado da operação com estatísticas
     * @throws \Exception
     */
    public function assignToEmployees(int $templateId, array $employeeIds, array $dates): array
    {
        $template = WorkShiftTemplate::with(['weeklySchedules', 'rotatingRule'])->findOrFail($templateId);

        DB::beginTransaction();

        try {
            $successCount = 0;
            $errors = [];

            foreach ($employeeIds as $employeeId) {
                try {
                    $employee = Employee::findOrFail($employeeId);

                    // Finaliza atribuições anteriores
                    $this->endCurrentAssignments($employeeId, $dates['effective_from']);

                    // Cria a nova atribuição
                    $assignment = EmployeeWorkShiftAssignment::create([
                        'employee_id' => $employeeId,
                        'template_id' => $templateId,
                        'cycle_start_date' => $dates['cycle_start_date'] ?? null,
                        'effective_from' => $dates['effective_from'],
                        'effective_until' => $dates['effective_until'] ?? null,
                        'assigned_by' => auth()->id(),
                    ]);

                    // Gera os horários na tabela work_schedules (opcional, para facilitar consultas)
                    if (isset($dates['generate_schedules']) && $dates['generate_schedules']) {
                        $this->generateWorkSchedules($assignment);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'employee_id' => $employeeId,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'assigned_count' => $successCount,
                'total_count' => count($employeeIds),
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Finaliza as atribuições atuais de um colaborador
     *
     * @param int $employeeId ID do colaborador
     * @param string $endDate Data de término
     * @return void
     */
    protected function endCurrentAssignments(int $employeeId, string $endDate): void
    {
        EmployeeWorkShiftAssignment::where('employee_id', $employeeId)
            ->active()
            ->update([
                'effective_until' => Carbon::parse($endDate)->subDay()->format('Y-m-d')
            ]);
    }

    /**
     * Remove a atribuição de jornada de um colaborador
     *
     * @param int $employeeId ID do colaborador
     * @return bool
     */
    public function unassignFromEmployee(int $employeeId): bool
    {
        $today = Carbon::now()->format('Y-m-d');

        return EmployeeWorkShiftAssignment::where('employee_id', $employeeId)
            ->active()
            ->update(['effective_until' => $today]) > 0;
    }

    /**
     * Retorna o horário de trabalho de um colaborador para uma data específica
     *
     * @param int $employeeId ID do colaborador
     * @param string $date Data no formato Y-m-d
     * @return array|null Horários do dia ou null se não houver
     */
    public function getEmployeeScheduleForDate(int $employeeId, string $date): ?array
    {
        $assignment = EmployeeWorkShiftAssignment::with(['template.weeklySchedules', 'template.rotatingRule'])
            ->where('employee_id', $employeeId)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            })
            ->first();

        if (!$assignment) {
            return null;
        }

        $template = $assignment->template;
        $targetDate = Carbon::parse($date);

        // Para templates semanais
        if ($template->type === 'weekly') {
            $dayOfWeek = $targetDate->dayOfWeek;
            $schedule = $template->weeklySchedules->firstWhere('day_of_week', $dayOfWeek);

            if (!$schedule || !$schedule->is_work_day) {
                return null;
            }

            return [
                'type' => 'weekly',
                'is_work_day' => true,
                'entry_1' => $schedule->entry_1 ? $schedule->entry_1->format('H:i:s') : null,
                'exit_1' => $schedule->exit_1 ? $schedule->exit_1->format('H:i:s') : null,
                'entry_2' => $schedule->entry_2 ? $schedule->entry_2->format('H:i:s') : null,
                'exit_2' => $schedule->exit_2 ? $schedule->exit_2->format('H:i:s') : null,
                'entry_3' => $schedule->entry_3 ? $schedule->entry_3->format('H:i:s') : null,
                'exit_3' => $schedule->exit_3 ? $schedule->exit_3->format('H:i:s') : null,
                'daily_hours' => $schedule->daily_hours,
            ];
        }

        // Para escalas rotativas
        if ($template->type === 'rotating_shift' && $template->rotatingRule) {
            $rule = $template->rotatingRule;
            $cycleStart = new \DateTime($assignment->cycle_start_date->format('Y-m-d'));
            $checkDate = new \DateTime($date);

            $isWorking = $this->calculator->isWorkingDay(
                $checkDate,
                $cycleStart,
                $rule->work_days,
                $rule->rest_days
            );

            if (!$isWorking) {
                return null;
            }

            return [
                'type' => 'rotating_shift',
                'is_work_day' => true,
                'entry_1' => $rule->shift_start_time ? $rule->shift_start_time->format('H:i:s') : null,
                'exit_1' => $rule->shift_end_time ? $rule->shift_end_time->format('H:i:s') : null,
                'entry_2' => null,
                'exit_2' => null,
                'entry_3' => null,
                'exit_3' => null,
                'daily_hours' => $rule->shift_duration_hours,
            ];
        }

        return null;
    }

    /**
     * Gera registros na tabela work_schedules baseado em um template
     * (Opcional - facilita consultas rápidas)
     *
     * @param EmployeeWorkShiftAssignment $assignment
     * @param int $daysAhead Quantos dias no futuro gerar (padrão: 90)
     * @return void
     */
    protected function generateWorkSchedules(EmployeeWorkShiftAssignment $assignment, int $daysAhead = 90): void
    {
        $template = $assignment->template;

        // Remove horários antigos deste template
        WorkSchedule::where('employee_id', $assignment->employee_id)
            ->where('source_template_id', $template->id)
            ->delete();

        if ($template->type === 'weekly') {
            // Para templates semanais, cria um registro para cada dia da semana
            foreach ($template->weeklySchedules as $weeklySchedule) {
                WorkSchedule::create([
                    'employee_id' => $assignment->employee_id,
                    'source_template_id' => $template->id,
                    'day_of_week' => $weeklySchedule->day_of_week,
                    'entry_1' => $weeklySchedule->entry_1,
                    'exit_1' => $weeklySchedule->exit_1,
                    'entry_2' => $weeklySchedule->entry_2,
                    'exit_2' => $weeklySchedule->exit_2,
                    'entry_3' => $weeklySchedule->entry_3,
                    'exit_3' => $weeklySchedule->exit_3,
                    'total_hours' => $weeklySchedule->daily_hours,
                    'effective_from' => $assignment->effective_from,
                    'effective_until' => $assignment->effective_until,
                ]);
            }
        } elseif ($template->type === 'rotating_shift' && $template->rotatingRule) {
            // Para escalas rotativas, gera os próximos X dias
            $rule = $template->rotatingRule;
            $startDate = Carbon::parse($assignment->effective_from);
            $endDate = $startDate->copy()->addDays($daysAhead);
            $cycleStartDate = new \DateTime($assignment->cycle_start_date->format('Y-m-d'));

            $workingDays = $this->calculator->getWorkingDaysInRange(
                $startDate->toDateTime(),
                $endDate->toDateTime(),
                $cycleStartDate,
                $rule->work_days,
                $rule->rest_days
            );

            foreach ($workingDays as $workDay) {
                $carbonDate = Carbon::instance($workDay);
                
                WorkSchedule::create([
                    'employee_id' => $assignment->employee_id,
                    'source_template_id' => $template->id,
                    'day_of_week' => $carbonDate->dayOfWeek,
                    'entry_1' => $rule->shift_start_time,
                    'exit_1' => $rule->shift_end_time,
                    'total_hours' => $rule->shift_duration_hours,
                    'effective_from' => $carbonDate->format('Y-m-d'),
                    'effective_until' => $carbonDate->format('Y-m-d'),
                ]);
            }
        }
    }

    /**
     * Retorna o histórico de jornadas de um colaborador
     *
     * @param int $employeeId ID do colaborador
     * @return Collection
     */
    public function getEmployeeHistory(int $employeeId): Collection
    {
        return EmployeeWorkShiftAssignment::with(['template', 'assignedBy'])
            ->where('employee_id', $employeeId)
            ->orderBy('effective_from', 'desc')
            ->get();
    }

    /**
     * Calcula dias de trabalho para uma escala rotativa em um intervalo
     *
     * @param int $templateId ID do template
     * @param string $cycleStartDate Data de início do ciclo
     * @param array $dateRange Array com 'start' e 'end'
     * @return array
     */
    public function calculateRotatingShiftDays(int $templateId, string $cycleStartDate, array $dateRange): array
    {
        $template = WorkShiftTemplate::with('rotatingRule')->findOrFail($templateId);

        if ($template->type !== 'rotating_shift' || !$template->rotatingRule) {
            throw new \Exception('Template não é do tipo escala rotativa.');
        }

        $rule = $template->rotatingRule;
        $startDate = new \DateTime($dateRange['start']);
        $endDate = new \DateTime($dateRange['end']);
        $cycleStart = new \DateTime($cycleStartDate);

        $workingDays = $this->calculator->getWorkingDaysInRange(
            $startDate,
            $endDate,
            $cycleStart,
            $rule->work_days,
            $rule->rest_days
        );

        return [
            'working_days_count' => count($workingDays),
            'working_days' => array_map(fn($date) => $date->format('Y-m-d'), $workingDays),
            'total_hours' => count($workingDays) * $rule->shift_duration_hours,
        ];
    }
}
