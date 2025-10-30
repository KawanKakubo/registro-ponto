<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkShiftTemplate;
use App\Models\TemplateWeeklySchedule;

class WorkShiftPresetsSeeder extends Seeder
{
    public function run(): void
    {
        $presets = [
            // Horário Padrão Prefeitura de Assaí
            [
                'name' => 'Padrão Prefeitura Assaí (40h)',
                'type' => 'weekly',
                'weekly_hours' => 40.00,
                'is_preset' => true,
                'schedules' => [
                    ['day' => 1, 'entry_1' => '08:00:00', 'exit_1' => '11:30:00', 'entry_2' => '13:00:00', 'exit_2' => '17:00:00'], // Segunda
                    ['day' => 2, 'entry_1' => '08:00:00', 'exit_1' => '11:30:00', 'entry_2' => '13:00:00', 'exit_2' => '17:00:00'], // Terça
                    ['day' => 3, 'entry_1' => '08:00:00', 'exit_1' => '11:30:00', 'entry_2' => '13:00:00', 'exit_2' => '17:00:00'], // Quarta
                    ['day' => 4, 'entry_1' => '08:00:00', 'exit_1' => '11:30:00', 'entry_2' => '13:00:00', 'exit_2' => '17:00:00'], // Quinta
                    ['day' => 5, 'entry_1' => '08:00:00', 'exit_1' => '11:30:00', 'entry_2' => '13:00:00', 'exit_2' => '17:00:00'], // Sexta
                    ['day' => 6, 'is_work_day' => false], // Sábado - Folga
                    ['day' => 0, 'is_work_day' => false], // Domingo - Folga
                ]
            ],
            // Horário Comercial 44h
            [
                'name' => 'Comercial (44h/semana)',
                'type' => 'weekly',
                'weekly_hours' => 44.00,
                'is_preset' => true,
                'schedules' => [
                    ['day' => 1, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00', 'entry_2' => '13:00:00', 'exit_2' => '18:00:00'], // Segunda
                    ['day' => 2, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00', 'entry_2' => '13:00:00', 'exit_2' => '18:00:00'], // Terça
                    ['day' => 3, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00', 'entry_2' => '13:00:00', 'exit_2' => '18:00:00'], // Quarta
                    ['day' => 4, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00', 'entry_2' => '13:00:00', 'exit_2' => '18:00:00'], // Quinta
                    ['day' => 5, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00', 'entry_2' => '13:00:00', 'exit_2' => '18:00:00'], // Sexta
                    ['day' => 6, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00', 'is_work_day' => true], // Sábado - 4h
                    ['day' => 0, 'is_work_day' => false], // Domingo - Folga
                ]
            ],
            // Manhã apenas
            [
                'name' => 'Turno Manhã (20h)',
                'type' => 'weekly',
                'weekly_hours' => 20.00,
                'is_preset' => true,
                'schedules' => [
                    ['day' => 1, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00'], // Segunda
                    ['day' => 2, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00'], // Terça
                    ['day' => 3, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00'], // Quarta
                    ['day' => 4, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00'], // Quinta
                    ['day' => 5, 'entry_1' => '08:00:00', 'exit_1' => '12:00:00'], // Sexta
                    ['day' => 6, 'is_work_day' => false], // Sábado - Folga
                    ['day' => 0, 'is_work_day' => false], // Domingo - Folga
                ]
            ],
            // Tarde apenas
            [
                'name' => 'Turno Tarde (20h)',
                'type' => 'weekly',
                'weekly_hours' => 20.00,
                'is_preset' => true,
                'schedules' => [
                    ['day' => 1, 'entry_1' => '13:00:00', 'exit_1' => '17:00:00'], // Segunda
                    ['day' => 2, 'entry_1' => '13:00:00', 'exit_1' => '17:00:00'], // Terça
                    ['day' => 3, 'entry_1' => '13:00:00', 'exit_1' => '17:00:00'], // Quarta
                    ['day' => 4, 'entry_1' => '13:00:00', 'exit_1' => '17:00:00'], // Quinta
                    ['day' => 5, 'entry_1' => '13:00:00', 'exit_1' => '17:00:00'], // Sexta
                    ['day' => 6, 'is_work_day' => false], // Sábado - Folga
                    ['day' => 0, 'is_work_day' => false], // Domingo - Folga
                ]
            ],
            // Escala 12x36 Diurno
            [
                'name' => 'Escala 12x36 Diurno',
                'type' => 'rotating_shift',
                'weekly_hours' => 42.00,
                'is_preset' => true,
                'rotating_rule' => [
                    'work_days' => 1,
                    'rest_days' => 1,
                    'shift_start_time' => '07:00:00',
                    'shift_end_time' => '19:00:00',
                ]
            ],
            // Escala 12x36 Noturno
            [
                'name' => 'Escala 12x36 Noturno',
                'type' => 'rotating_shift',
                'weekly_hours' => 42.00,
                'is_preset' => true,
                'rotating_rule' => [
                    'work_days' => 1,
                    'rest_days' => 1,
                    'shift_start_time' => '19:00:00',
                    'shift_end_time' => '07:00:00',
                ]
            ],
        ];

        foreach ($presets as $presetData) {
            $template = WorkShiftTemplate::firstOrCreate(
                ['name' => $presetData['name']],
                [
                    'type' => $presetData['type'],
                    'weekly_hours' => $presetData['weekly_hours'],
                    'is_preset' => $presetData['is_preset'],
                ]
            );

            if ($template->type === 'weekly' && isset($presetData['schedules'])) {
                foreach ($presetData['schedules'] as $scheduleData) {
                    TemplateWeeklySchedule::firstOrCreate(
                        [
                            'template_id' => $template->id,
                            'day_of_week' => $scheduleData['day'],
                        ],
                        [
                            'is_work_day' => $scheduleData['is_work_day'] ?? true,
                            'entry_1' => $scheduleData['entry_1'] ?? null,
                            'exit_1' => $scheduleData['exit_1'] ?? null,
                            'entry_2' => $scheduleData['entry_2'] ?? null,
                            'exit_2' => $scheduleData['exit_2'] ?? null,
                        ]
                    );
                }
            } elseif ($template->type === 'rotating_shift' && isset($presetData['rotating_rule'])) {
                $template->rotatingRule()->firstOrCreate(
                    ['template_id' => $template->id],
                    $presetData['rotating_rule']
                );
            }
        }

        $this->command->info('✅ ' . count($presets) . ' templates de jornada pré-configurados criados!');
    }
}
