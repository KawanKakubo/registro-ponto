<?php

namespace App\Services;

use App\Models\WorkShiftTemplate;
use App\Models\TemplateWeeklySchedule;
use App\Models\TemplateRotatingRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class WorkShiftTemplateService
{
    /**
     * Cria um novo template de jornada
     *
     * @param array $data Dados do template
     * @return WorkShiftTemplate
     * @throws \Exception
     */
    public function createTemplate(array $data): WorkShiftTemplate
    {
        DB::beginTransaction();

        try {
            $template = WorkShiftTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'is_preset' => $data['is_preset'] ?? false,
                'weekly_hours' => $data['weekly_hours'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Se for template semanal, cria os horários
            if ($template->type === 'weekly' && isset($data['weekly_schedules'])) {
                foreach ($data['weekly_schedules'] as $schedule) {
                    TemplateWeeklySchedule::create([
                        'template_id' => $template->id,
                        'day_of_week' => $schedule['day_of_week'],
                        'entry_1' => $schedule['entry_1'] ?? null,
                        'exit_1' => $schedule['exit_1'] ?? null,
                        'entry_2' => $schedule['entry_2'] ?? null,
                        'exit_2' => $schedule['exit_2'] ?? null,
                        'entry_3' => $schedule['entry_3'] ?? null,
                        'exit_3' => $schedule['exit_3'] ?? null,
                        'is_work_day' => $schedule['is_work_day'] ?? true,
                    ]);
                }
            }

            // Se for escala rotativa, cria a regra
            if ($template->type === 'rotating_shift' && isset($data['rotating_rule'])) {
                TemplateRotatingRule::create([
                    'template_id' => $template->id,
                    'work_days' => $data['rotating_rule']['work_days'],
                    'rest_days' => $data['rotating_rule']['rest_days'],
                    'shift_start_time' => $data['rotating_rule']['shift_start_time'] ?? null,
                    'shift_end_time' => $data['rotating_rule']['shift_end_time'] ?? null,
                ]);
            }

            DB::commit();
            return $template->fresh(['weeklySchedules', 'rotatingRule']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atualiza um template existente
     *
     * @param int $id ID do template
     * @param array $data Dados atualizados
     * @return WorkShiftTemplate
     * @throws \Exception
     */
    public function updateTemplate(int $id, array $data): WorkShiftTemplate
    {
        DB::beginTransaction();

        try {
            $template = WorkShiftTemplate::findOrFail($id);

            // Não permitir editar presets do sistema
            if ($template->is_preset && !isset($data['allow_preset_edit'])) {
                throw new \Exception('Templates preset do sistema não podem ser editados.');
            }

            $template->update([
                'name' => $data['name'] ?? $template->name,
                'description' => $data['description'] ?? $template->description,
                'weekly_hours' => $data['weekly_hours'] ?? $template->weekly_hours,
            ]);

            // Atualizar horários semanais se fornecidos
            if ($template->type === 'weekly' && isset($data['weekly_schedules'])) {
                // Remove os antigos
                $template->weeklySchedules()->delete();

                // Cria os novos
                foreach ($data['weekly_schedules'] as $schedule) {
                    TemplateWeeklySchedule::create([
                        'template_id' => $template->id,
                        'day_of_week' => $schedule['day_of_week'],
                        'entry_1' => $schedule['entry_1'] ?? null,
                        'exit_1' => $schedule['exit_1'] ?? null,
                        'entry_2' => $schedule['entry_2'] ?? null,
                        'exit_2' => $schedule['exit_2'] ?? null,
                        'entry_3' => $schedule['entry_3'] ?? null,
                        'exit_3' => $schedule['exit_3'] ?? null,
                        'is_work_day' => $schedule['is_work_day'] ?? true,
                    ]);
                }
            }

            // Atualizar regra de escala rotativa se fornecida
            if ($template->type === 'rotating_shift' && isset($data['rotating_rule'])) {
                $template->rotatingRule()->updateOrCreate(
                    ['template_id' => $template->id],
                    [
                        'work_days' => $data['rotating_rule']['work_days'],
                        'rest_days' => $data['rotating_rule']['rest_days'],
                        'shift_start_time' => $data['rotating_rule']['shift_start_time'] ?? null,
                        'shift_end_time' => $data['rotating_rule']['shift_end_time'] ?? null,
                    ]
                );
            }

            DB::commit();
            return $template->fresh(['weeklySchedules', 'rotatingRule']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deleta um template
     *
     * @param int $id ID do template
     * @return bool
     * @throws \Exception
     */
    public function deleteTemplate(int $id): bool
    {
        $template = WorkShiftTemplate::findOrFail($id);

        // Não permitir deletar presets
        if ($template->is_preset) {
            throw new \Exception('Templates preset do sistema não podem ser deletados.');
        }

        // Não permitir deletar templates em uso
        if ($template->getCurrentEmployeesCount() > 0) {
            throw new \Exception('Este template está em uso por colaboradores e não pode ser deletado.');
        }

        return $template->delete();
    }

    /**
     * Duplica um template existente
     *
     * @param int $id ID do template a duplicar
     * @param string $newName Nome do novo template
     * @return WorkShiftTemplate
     */
    public function duplicateTemplate(int $id, string $newName): WorkShiftTemplate
    {
        DB::beginTransaction();

        try {
            $original = WorkShiftTemplate::with(['weeklySchedules', 'rotatingRule'])->findOrFail($id);

            $data = [
                'name' => $newName,
                'description' => $original->description,
                'type' => $original->type,
                'is_preset' => false,
                'weekly_hours' => $original->weekly_hours,
                'created_by' => auth()->id(),
            ];

            // Copiar horários semanais
            if ($original->weeklySchedules->isNotEmpty()) {
                $data['weekly_schedules'] = $original->weeklySchedules->map(function ($schedule) {
                    return [
                        'day_of_week' => $schedule->day_of_week,
                        'entry_1' => $schedule->entry_1 ? $schedule->entry_1->format('H:i:s') : null,
                        'exit_1' => $schedule->exit_1 ? $schedule->exit_1->format('H:i:s') : null,
                        'entry_2' => $schedule->entry_2 ? $schedule->entry_2->format('H:i:s') : null,
                        'exit_2' => $schedule->exit_2 ? $schedule->exit_2->format('H:i:s') : null,
                        'entry_3' => $schedule->entry_3 ? $schedule->entry_3->format('H:i:s') : null,
                        'exit_3' => $schedule->exit_3 ? $schedule->exit_3->format('H:i:s') : null,
                        'is_work_day' => $schedule->is_work_day,
                    ];
                })->toArray();
            }

            // Copiar regra de escala rotativa
            if ($original->rotatingRule) {
                $data['rotating_rule'] = [
                    'work_days' => $original->rotatingRule->work_days,
                    'rest_days' => $original->rotatingRule->rest_days,
                    'shift_start_time' => $original->rotatingRule->shift_start_time ? 
                        $original->rotatingRule->shift_start_time->format('H:i:s') : null,
                    'shift_end_time' => $original->rotatingRule->shift_end_time ? 
                        $original->rotatingRule->shift_end_time->format('H:i:s') : null,
                ];
            }

            $newTemplate = $this->createTemplate($data);

            DB::commit();
            return $newTemplate;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lista templates com estatísticas de uso
     *
     * @return Collection
     */
    public function getTemplatesWithStats(): Collection
    {
        return WorkShiftTemplate::with(['weeklySchedules', 'rotatingRule'])
            ->withCount(['assignments as active_employees_count' => function ($query) {
                $query->where('effective_from', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('effective_until')
                            ->orWhere('effective_until', '>=', now());
                    });
            }])
            ->orderBy('is_preset', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Busca templates por tipo
     *
     * @param string $type Tipo do template ('weekly' ou 'rotating_shift')
     * @return Collection
     */
    public function getTemplatesByType(string $type): Collection
    {
        return WorkShiftTemplate::where('type', $type)
            ->with(['weeklySchedules', 'rotatingRule'])
            ->orderBy('is_preset', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Retorna apenas os presets
     *
     * @return Collection
     */
    public function getPresets(): Collection
    {
        return WorkShiftTemplate::presets()
            ->with(['weeklySchedules', 'rotatingRule'])
            ->orderBy('name')
            ->get();
    }
}
