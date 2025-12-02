<?php

namespace App\Console\Commands;

use App\Models\WorkShiftTemplate;
use App\Models\TemplateWeeklySchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportWorkShiftTemplatesFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vinculos:import-templates {csv_path} {--force : Recriar templates existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa templates de jornada a partir do CSV de vínculos (extrai horários reais e cria jornadas únicas por padrão)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvPath = $this->argument('csv_path');
        $force = $this->option('force');
        
        if (!file_exists($csvPath)) {
            $this->error("❌ Arquivo não encontrado: {$csvPath}");
            return 1;
        }

        $this->info("📂 Lendo e analisando arquivo: {$csvPath}");
        
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->error("❌ Não foi possível abrir o arquivo CSV");
            return 1;
        }

        // Ler header
        $header = fgetcsv($handle);
        $horarioIndex = array_search('HORÁRIO', $header);
        
        if ($horarioIndex === false) {
            $this->error("❌ Coluna 'HORÁRIO' não encontrada no CSV");
            fclose($handle);
            return 1;
        }

        // Coletar padrões de horários
        $jornadas = [];
        
        while (($row = fgetcsv($handle)) !== false) {
            if (isset($row[$horarioIndex])) {
                $horario = trim($row[$horarioIndex]);
                
                // Extrair ID e descrição completa
                // Exemplo: "7 - SAÚDE -07:30-11:30 E 13:00-17:00"
                if (preg_match('/^(\d+)\s*-\s*(.+)$/', $horario, $matches)) {
                    $id = (int) $matches[1];
                    $descricao = trim($matches[2]);
                    
                    // Extrair horários reais do campo
                    $schedules = $this->parseScheduleFromDescription($descricao);
                    
                    $jornadas[$id] = [
                        'id' => $id,
                        'description' => $descricao,
                        'schedules' => $schedules,
                    ];
                }
            }
        }

        fclose($handle);
        ksort($jornadas);

        $this->info("✅ Encontradas " . count($jornadas) . " jornadas únicas");
        $this->newLine();

        if (!$this->confirm('Deseja criar/atualizar os templates de jornada?', true)) {
            $this->info('Operação cancelada');
            return 0;
        }

        $this->newLine();
        $created = 0;
        $updated = 0;
        $skipped = 0;
        
        $bar = $this->output->createProgressBar(count($jornadas));
        $bar->start();
        
        foreach ($jornadas as $jornadaData) {
            try {
                $id = $jornadaData['id'];
                $description = $jornadaData['description'];
                $schedules = $jornadaData['schedules'];
                
                $existing = WorkShiftTemplate::find($id);
                
                if ($existing && !$force) {
                    $skipped++;
                } else {
                    if ($existing && $force) {
                        // Deletar horários antigos
                        TemplateWeeklySchedule::where('template_id', $id)->delete();
                        $existing->delete();
                    }
                    
                    // Criar nome descritivo
                    try {
                        $name = $this->generateTemplateName($description, $schedules, $id);
                    } catch (\Exception $e) {
                        $name = "Jornada {$id}";
                    }
                    
                    // Calcular carga horária semanal
                    try {
                        $weeklyHours = $this->calculateWeeklyHours($schedules);
                    } catch (\Exception $e) {
                        $weeklyHours = 40.00;
                    }
                    
                    // Criar template
                    $template = WorkShiftTemplate::create([
                        'id' => $id,
                        'name' => $name,
                        'description' => $description,
                        'type' => 'weekly',
                        'weekly_hours' => $weeklyHours,
                        'is_preset' => false,
                        'created_by' => null,
                    ]);
                    
                    // Criar horários semanais (segunda a sexta com mesmo horário)
                    if (!empty($schedules) && isset($schedules['entry_1']) && isset($schedules['exit_1'])) {
                        try {
                            foreach ([1, 2, 3, 4, 5] as $dayOfWeek) {
                                TemplateWeeklySchedule::create([
                                    'template_id' => $template->id,
                                    'day_of_week' => $dayOfWeek,
                                    'entry_1' => $schedules['entry_1'] . ':00',
                                    'exit_1' => $schedules['exit_1'] . ':00',
                                    'entry_2' => isset($schedules['entry_2']) ? $schedules['entry_2'] . ':00' : null,
                                    'exit_2' => isset($schedules['exit_2']) ? $schedules['exit_2'] . ':00' : null,
                                    'is_work_day' => true,
                                ]);
                            }
                        } catch (\Exception $e) {
                            // Se falhar ao criar horários, continuar sem eles
                            $this->warn("\n⚠️  Não foi possível criar horários para jornada {$id}: " . $e->getMessage());
                        }
                    } else {
                        $this->warn("\n⚠️  Horários não extraídos para jornada {$id}. Schedules: " . json_encode($schedules));
                    }
                    
                    if ($existing) {
                        $updated++;
                    } else {
                        $created++;
                    }
                }
                
                } catch (\Exception $e) {
                    $this->warn("\n⚠️  Erro ao processar jornada {$id}: " . $e->getMessage());
                    $skipped++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);

            $this->info("✅ Importação concluída!");
            $this->table(
                ['Status', 'Quantidade'],
                [
                    ['Templates criados', $created],
                    ['Templates atualizados', $updated],
                    ['Templates ignorados', $skipped],
                    ['Total processado', count($jornadas)],
                ]
            );

            $this->newLine();
            $this->info('✅ Jornadas criadas com horários extraídos do CSV!');
            $this->info('📋 Acesse /work-shift-templates para revisar e ajustar.');

            return 0;
    }
    
    /**
     * Extrai horários de entrada e saída da descrição
     */
    protected function parseScheduleFromDescription(string $description): array
    {
        $schedules = [];
        
        // Padrões comuns:
        // "SAÚDE -07:30-11:30 E 13:00-17:00"
        // "07:30-11:00 E 12:30-17:00"
        // "08:00-11:30 - 13:00-16:30"
        // "SEC - 05:30-10:45 E 11:45-14:30"
        // "NOVO - 08-11:30 E 13-17" (formato com minutos misturados)
        
        // Remover prefixos (SEC, SAÚDE, NOVO, etc)
        $cleaned = preg_replace('/^[A-ZÇÃÁÀÂÊÉÍÓÔÕÚÜ\s]+-\s*/', '', $description);
        $cleaned = trim($cleaned);
        
        // Normalizar APENAS formatos sem minutos (HH-HH, não HH:MM)
        // Usar negative lookbehind/lookahead para não pegar HH:MM-HH:MM
        // Padrão: número de 1-2 dígitos seguido de hífen, MAS sem dois pontos antes
        $cleaned = preg_replace('/(?<!\d:)(\d{1,2})-(?=\d{1,2}(?:[^\d:]|$))/', '$1:00-', $cleaned);
        $cleaned = preg_replace('/(?<!\d:)(\d{1,2})(?=[^\d:]*$)/', '$1:00', $cleaned);
        
        // Padrão 1: HH:MM-HH:MM E HH:MM-HH:MM (dois períodos)
        if (preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s+E\s+(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/i', $cleaned, $m)) {
            $schedules['entry_1'] = sprintf('%02d:%02d', $m[1], $m[2]);
            $schedules['exit_1'] = sprintf('%02d:%02d', $m[3], $m[4]);
            $schedules['entry_2'] = sprintf('%02d:%02d', $m[5], $m[6]);
            $schedules['exit_2'] = sprintf('%02d:%02d', $m[7], $m[8]);
        }
        // Padrão 2: HH:MM-HH:MM - HH:MM-HH:MM (com hífen duplo ao invés de E)
        elseif (preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/', $cleaned, $m)) {
            $schedules['entry_1'] = sprintf('%02d:%02d', $m[1], $m[2]);
            $schedules['exit_1'] = sprintf('%02d:%02d', $m[3], $m[4]);
            $schedules['entry_2'] = sprintf('%02d:%02d', $m[5], $m[6]);
            $schedules['exit_2'] = sprintf('%02d:%02d', $m[7], $m[8]);
        }
        // Padrão 3: HH:MM-HH:MM e HH:MM-HH:MM (com 'e' minúsculo)
        elseif (preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s+e\s+(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/i', $cleaned, $m)) {
            $schedules['entry_1'] = sprintf('%02d:%02d', $m[1], $m[2]);
            $schedules['exit_1'] = sprintf('%02d:%02d', $m[3], $m[4]);
            $schedules['entry_2'] = sprintf('%02d:%02d', $m[5], $m[6]);
            $schedules['exit_2'] = sprintf('%02d:%02d', $m[7], $m[8]);
        }
        // Padrão 4: Apenas um período HH:MM-HH:MM
        elseif (preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/', $cleaned, $m)) {
            $schedules['entry_1'] = sprintf('%02d:%02d', $m[1], $m[2]);
            $schedules['exit_1'] = sprintf('%02d:%02d', $m[3], $m[4]);
        }
        
        return $schedules;
    }
    
    /**
     * Gera nome descritivo para o template
     */
    protected function generateTemplateName(string $description, array $schedules, int $id): string
    {
        // Extrair departamento se houver
        if (preg_match('/^([A-ZÇÃÁÀÂÊÉÍÓÔÕÚÜ\s]+)\s*-/', $description, $matches)) {
            $dept = trim($matches[1]);
        } else {
            $dept = 'Geral';
        }
        
        // Gerar nome baseado nos horários
        if (isset($schedules['entry_1']) && isset($schedules['exit_1']) && 
            $this->isValidTime($schedules['entry_1']) && $this->isValidTime($schedules['exit_1'])) {
            $name = "{$dept} - {$schedules['entry_1']} às {$schedules['exit_1']}";
            
            if (isset($schedules['entry_2']) && isset($schedules['exit_2']) &&
                $this->isValidTime($schedules['entry_2']) && $this->isValidTime($schedules['exit_2'])) {
                $name .= " e {$schedules['entry_2']} às {$schedules['exit_2']}";
            }
        } else {
            $name = "{$dept} (ID {$id})";
        }
        
        return mb_substr($name, 0, 100); // Limitar tamanho
    }
    
    /**
     * Valida se o horário está no formato correto HH:MM
     */
    protected function isValidTime(?string $time): bool
    {
        if (empty($time)) {
            return false;
        }
        
        return (bool) preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }
    
    /**
     * Calcula carga horária semanal
     */
    protected function calculateWeeklyHours(array $schedules): float
    {
        $dailyHours = 0;
        
        try {
            if (isset($schedules['entry_1']) && isset($schedules['exit_1']) &&
                $this->isValidTime($schedules['entry_1']) && $this->isValidTime($schedules['exit_1'])) {
                $entry1 = \Carbon\Carbon::createFromFormat('H:i', $schedules['entry_1']);
                $exit1 = \Carbon\Carbon::createFromFormat('H:i', $schedules['exit_1']);
                $dailyHours += $exit1->diffInMinutes($entry1) / 60;
            }
            
            if (isset($schedules['entry_2']) && isset($schedules['exit_2']) &&
                $this->isValidTime($schedules['entry_2']) && $this->isValidTime($schedules['exit_2'])) {
                $entry2 = \Carbon\Carbon::createFromFormat('H:i', $schedules['entry_2']);
                $exit2 = \Carbon\Carbon::createFromFormat('H:i', $schedules['exit_2']);
                $dailyHours += $exit2->diffInMinutes($entry2) / 60;
            }
        } catch (\Exception $e) {
            // Se falhar ao calcular, retornar padrão 40h
            return 40.00;
        }
        
        // 5 dias por semana
        $weeklyHours = round($dailyHours * 5, 2);
        
        // Se zerou ou deu negativo, retornar padrão
        return $weeklyHours > 0 ? $weeklyHours : 40.00;
    }
}
