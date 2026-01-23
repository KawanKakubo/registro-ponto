<?php

namespace App\Jobs;

use App\Models\Person;
use App\Models\EmployeeRegistration;
use App\Models\WorkShiftTemplate;
use App\Models\TemplateWeeklySchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportVinculosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    protected $csvPath;
    protected $importId;
    protected $userId;

    /**
     * Cria uma nova instância do job
     *
     * @param string $csvPath Caminho do arquivo CSV no storage
     * @param int $importId ID do registro de importação
     * @param int|null $userId ID do usuário que iniciou a importação
     */
    public function __construct(string $csvPath, int $importId, ?int $userId = null)
    {
        $this->csvPath = $csvPath;
        $this->importId = $importId;
        $this->userId = $userId;
    }

    /**
     * Executa o job
     */
    public function handle(): void
    {
        $results = [
            'total' => 0,
            'pessoas_criadas' => 0,
            'pessoas_atualizadas' => 0,
            'vinculos_criados' => 0,
            'vinculos_atualizados' => 0,
            'jornadas_associadas' => 0,
            'templates_criados' => 0,
            'erros' => [],
        ];

        $errorDetails = [];
        $templatesCache = []; // Cache para evitar múltiplas consultas ao banco

        try {
            // Abrir arquivo CSV
            $filePath = Storage::path($this->csvPath);
            
            if (!file_exists($filePath)) {
                throw new \Exception("Arquivo CSV não encontrado: {$filePath}");
            }

            $handle = fopen($filePath, 'r');
            
            if ($handle === false) {
                throw new \Exception("Não foi possível abrir o arquivo CSV");
            }

            // Ler header
            $header = fgetcsv($handle);
            
            // Validar header - HORÁRIO_LIMPO é opcional
            $requiredColumns = ['NOME', 'Nº PIS/PASEP', 'Nº IDENTIFICADOR', 'HORÁRIO'];
            $missingColumns = array_diff($requiredColumns, $header);
            
            if (!empty($missingColumns)) {
                throw new \Exception("Colunas faltando no CSV: " . implode(', ', $missingColumns));
            }

            // Mapear índices das colunas
            $colIndexes = array_flip($header);

            $lineNumber = 1; // Header é linha 1

            // Processar cada linha
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                $results['total']++;

                try {
                    // Extrair dados da linha
                    $nome = trim($row[$colIndexes['NOME']] ?? '');
                    $pis = $this->cleanPis(trim($row[$colIndexes['Nº PIS/PASEP']] ?? ''));
                    $matricula = $this->cleanMatricula(trim($row[$colIndexes['Nº IDENTIFICADOR']] ?? ''));
                    $horario = trim($row[$colIndexes['HORÁRIO']] ?? '');

                    // Validar campos obrigatórios
                    if (empty($pis)) {
                        throw new \Exception("PIS/PASEP é obrigatório");
                    }

                    if (empty($matricula)) {
                        throw new \Exception("Matrícula (Nº IDENTIFICADOR) é obrigatória");
                    }

                    if (empty($nome)) {
                        throw new \Exception("NOME é obrigatório");
                    }

                    // PASSO 0: Extrair ID da Jornada e criar template se necessário (ANTES da transação)
                    $workShiftTemplateId = $this->parseWorkShiftId($horario);
                    
                    if ($workShiftTemplateId) {
                        // Verificar cache primeiro
                        if (!isset($templatesCache[$workShiftTemplateId])) {
                            // Buscar no banco
                            $templatesCache[$workShiftTemplateId] = WorkShiftTemplate::find($workShiftTemplateId);
                        }
                        
                        // Se template não existe, criar automaticamente ANTES da transação
                        if (!$templatesCache[$workShiftTemplateId]) {
                            try {
                                $template = $this->createTemplateFromHorario($workShiftTemplateId, $horario);
                                
                                // Forçar refresh do modelo para garantir que está persistido
                                $template->refresh();
                                
                                // Armazenar no cache
                                $templatesCache[$workShiftTemplateId] = $template;
                                $results['templates_criados']++;
                                
                                Log::info("Template {$workShiftTemplateId} criado automaticamente: {$horario}");
                            } catch (\Exception $e) {
                                Log::error("Erro ao criar template {$workShiftTemplateId}: " . $e->getMessage());
                                $templatesCache[$workShiftTemplateId] = null;
                            }
                        }
                    }

                    // Processar importação em transação
                    DB::transaction(function () use (
                        $nome, 
                        $pis, 
                        $matricula, 
                        $horario,
                        $workShiftTemplateId,
                        &$results,
                        &$templatesCache,
                        $lineNumber
                    ) {
                        // PASSO 1: Encontrar ou criar Pessoa pelo PIS
                        // Se a pessoa JÁ existe (foi importada antes), apenas busca
                        // Se não existe, cria nova
                        $personExists = Person::where('pis_pasep', $pis)->exists();
                        
                        $person = Person::updateOrCreate(
                            ['pis_pasep' => $pis], // Buscar por PIS
                            ['full_name' => $nome] // Atualizar/criar com nome
                        );
                        
                        // Verificar se foi criado ou atualizado
                        if ($person->wasRecentlyCreated) {
                            $results['pessoas_criadas']++;
                            Log::debug("Linha {$lineNumber}: Pessoa criada - PIS: {$pis}, Nome: {$nome}");
                        } else {
                            $results['pessoas_atualizadas']++;
                            Log::debug("Linha {$lineNumber}: Pessoa ENCONTRADA - PIS: {$pis}, Nome existente: {$person->full_name}");
                        }

                        // PASSO 2: Encontrar ou criar Vínculo pela Matrícula
                        // IMPORTANTE: Se vínculo já existe, apenas busca (não atualiza)
                        // Se não existe, cria novo com dados mínimos
                        $registration = EmployeeRegistration::where('matricula', $matricula)->first();
                        
                        if ($registration) {
                            // Vínculo JÁ EXISTE - apenas busca, SEM atualizar (preserva dados completos)
                            $results['vinculos_atualizados']++;
                            Log::debug("Linha {$lineNumber}: Vínculo ENCONTRADO - Matrícula: {$matricula}, Preservando dados existentes");
                        } else {
                            // Vínculo NÃO EXISTE - cria novo com dados mínimos
                            $registration = EmployeeRegistration::create([
                                'matricula' => $matricula,
                                'person_id' => $person->id,
                                'establishment_id' => 1, // Default
                                'department_id' => null,
                                'admission_date' => now(), // Data padrão
                                'position' => null,
                                'status' => 'active',
                            ]);
                            
                            $results['vinculos_criados']++;
                            Log::debug("Linha {$lineNumber}: Vínculo CRIADO - Matrícula: {$matricula}, Person ID: {$person->id}");
                        }

                        // PASSO 3: Associar Jornada ao Vínculo (se ID válido e template existe)
                        // CRÍTICO: Esta etapa SEMPRE roda, mesmo se pessoa/vínculo já existiam
                        if ($workShiftTemplateId) {
                            Log::debug("Linha {$lineNumber}: Processando jornada - Template ID: {$workShiftTemplateId}");
                            
                            if (isset($templatesCache[$workShiftTemplateId]) && $templatesCache[$workShiftTemplateId]) {
                                // Verificar se já existe uma atribuição ATIVA para este template
                                $existingAssignment = $registration->workShiftAssignments()
                                    ->where('template_id', $workShiftTemplateId)
                                    ->where('effective_from', '<=', now())
                                    ->where(function ($query) {
                                        $query->whereNull('effective_until')
                                            ->orWhere('effective_until', '>=', now());
                                    })
                                    ->first();

                                if (!$existingAssignment) {
                                    // CRÍTICO: Usar data de admissão ou início do ano como effective_from
                                    // para garantir que a jornada cubra registros de ponto existentes
                                    $effectiveFrom = $registration->admission_date ?? now()->startOfYear();
                                    
                                    $registration->workShiftAssignments()->create([
                                        'template_id' => $workShiftTemplateId,
                                        'effective_from' => $effectiveFrom,
                                        'effective_until' => null,
                                        'assigned_by' => $this->userId,
                                        'assigned_at' => now(),
                                    ]);
                                    $results['jornadas_associadas']++;
                                    Log::info("Linha {$lineNumber}: ✓ Jornada ASSOCIADA - Template ID: {$workShiftTemplateId}, Vínculo: {$matricula}, Vigência desde: {$effectiveFrom}");
                                } else {
                                    Log::debug("Linha {$lineNumber}: Jornada já existe (não duplicada) - Template ID: {$workShiftTemplateId}");
                                }
                            } else {
                                Log::warning("Linha {$lineNumber}: ✗ Template {$workShiftTemplateId} NÃO encontrado! Jornada não associada.");
                            }
                        } else {
                            Log::debug("Linha {$lineNumber}: Sem template ID (horário vazio ou inválido)");
                        }
                    });

                } catch (\Exception $e) {
                    // Registrar erro
                    $errorDetails[] = [
                        'line' => $lineNumber,
                        'data' => $row,
                        'errors' => [$e->getMessage()],
                    ];
                }
            }

            fclose($handle);

            // Salvar resultados no storage
            $results['erros'] = $errorDetails; // Adicionar detalhes dos erros ao array de resultados
            $this->saveResults($results, $errorDetails);

        } catch (\Exception $e) {
            Log::error("Erro no ImportVinculosJob: " . $e->getMessage(), [
                'import_id' => $this->importId,
                'trace' => $e->getTraceAsString(),
            ]);

            // Atualizar status de importação como falhado
            DB::table('vinculo_imports')
                ->where('id', $this->importId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            throw $e;
        }
    }

    /**
     * Limpa o PIS removendo formatação e validando tamanho
     */
    protected function cleanPis(string $pis): string
    {
        // Remover tudo exceto números
        $cleaned = preg_replace('/[^0-9]/', '', $pis);
        
        // Se tiver mais de 11 dígitos, pegar apenas os primeiros 11
        if (strlen($cleaned) > 11) {
            Log::warning("PIS com mais de 11 dígitos detectado: {$pis}, usando apenas os primeiros 11 dígitos");
            $cleaned = substr($cleaned, 0, 11);
        }
        
        return $cleaned;
    }

    /**
     * Limpa a matrícula removendo ".0" no final (comum em exports do Excel)
     */
    protected function cleanMatricula(string $matricula): string
    {
        // Remover ".0" no final (comum quando Excel converte números)
        $cleaned = preg_replace('/\.0+$/', '', $matricula);
        
        return trim($cleaned);
    }

    /**
     * Extrai o ID da jornada do campo HORÁRIO
     * 
     * Exemplo: "7 - SAÚDE -07:30-11:30..." → 7
     *
     * @param string $horario Campo HORÁRIO do CSV
     * @return int|null ID da jornada ou null se não encontrado
     */
    protected function parseWorkShiftId(string $horario): ?int
    {
        if (empty($horario)) {
            return null;
        }

        // Padrão: número no início da string seguido de espaço e hífen
        // Exemplos: "7 - SAÚDE...", "219 - SEC...", "12 - ADMIN..."
        if (preg_match('/^(\d+)\s*-/', $horario, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Salva os resultados da importação no storage e atualiza o registro no banco
     */
    protected function saveResults(array $results, array $errorDetails): void
    {
        // Salvar resumo em JSON
        $summaryPath = "vinculo-imports/results-{$this->importId}.json";
        Storage::put($summaryPath, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Salvar detalhes dos erros em JSON separado
        if (!empty($errorDetails)) {
            $errorsPath = "vinculo-imports/errors-{$this->importId}.json";
            Storage::put($errorsPath, json_encode($errorDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // Atualizar registro de importação no banco de dados
        DB::table('vinculo_imports')
            ->where('id', $this->importId)
            ->update([
                'pessoas_criadas' => $results['pessoas_criadas'],
                'pessoas_atualizadas' => $results['pessoas_atualizadas'],
                'vinculos_criados' => $results['vinculos_criados'],
                'vinculos_atualizados' => $results['vinculos_atualizados'],
                'jornadas_associadas' => $results['jornadas_associadas'],
                'templates_criados' => $results['templates_criados'] ?? 0,
                'erros' => count($errorDetails),
                'status' => count($errorDetails) > 0 ? 'completed' : 'completed',
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

        Log::info("Importação de vínculos concluída", [
            'import_id' => $this->importId,
            'total' => $results['total'],
            'pessoas_criadas' => $results['pessoas_criadas'],
            'pessoas_atualizadas' => $results['pessoas_atualizadas'],
            'vinculos_criados' => $results['vinculos_criados'],
            'vinculos_criados' => $results['vinculos_criados'],
            'vinculos_atualizados' => $results['vinculos_atualizados'],
            'jornadas_associadas' => $results['jornadas_associadas'],
            'templates_criados' => $results['templates_criados'] ?? 0,
            'erros' => count($errorDetails),
        ]);
    }

    /**
     * Cria um template de jornada automaticamente a partir do campo HORÁRIO do CSV
     * 
     * @param int $id ID do template a ser criado
     * @param string $horario Campo HORÁRIO completo do CSV (ex: "7 - SAÚDE -07:30-11:30 E 13:00-17:00")
     * @return WorkShiftTemplate Template criado
     */
    protected function createTemplateFromHorario(int $id, string $horario): WorkShiftTemplate
    {
        // Usar uma transação separada para criar o template
        return DB::transaction(function () use ($id, $horario) {
            // Extrair descrição (tudo após o ID e hífen)
            // Exemplo: "7 - SAÚDE -07:30-11:30 E 13:00-17:00" → "SAÚDE -07:30-11:30 E 13:00-17:00"
            $description = preg_replace('/^\d+\s*-\s*/', '', $horario);
            
            // Extrair horários de entrada/saída
            $schedules = $this->parseScheduleFromDescription($description);
            
            // Gerar nome descritivo
            $name = $this->generateTemplateName($description, $schedules, $id);
            
            // Calcular carga horária semanal
            $weeklyHours = $this->calculateWeeklyHours($schedules);
            
            // CRITICAL: Inserir com ID específico usando INSERT direto para bypassar sequence
            $created = DB::insert('INSERT INTO work_shift_templates (id, name, description, type, weekly_hours, is_preset, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())', [
                $id,
                $name,
                $description,
                'weekly',
                $weeklyHours,
                false,
                $this->userId,
            ]);
            
            if (!$created) {
                throw new \Exception("Falha ao criar template com ID {$id}");
            }
            
            // Buscar o template criado
            $template = WorkShiftTemplate::find($id);
            
            if (!$template) {
                throw new \Exception("Template {$id} não encontrado após criação");
            }
            
            // Criar horários semanais (segunda a sexta) se conseguimos extrair os horários
            if (!empty($schedules) && isset($schedules['entry_1']) && isset($schedules['exit_1'])) {
                foreach ([1, 2, 3, 4, 5] as $dayOfWeek) {
                    TemplateWeeklySchedule::create([
                        'template_id' => $template->id,
                        'day_of_week' => $dayOfWeek,
                        'entry_1' => $schedules['entry_1'] . ':00',
                        'exit_1' => $schedules['exit_1'] . ':00',
                        'entry_2' => isset($schedules['entry_2']) ? $schedules['entry_2'] . ':00' : null,
                        'exit_2' => isset($schedules['exit_2']) ? $schedules['exit_2'] . ':00' : null,
                        'is_work_day' => true,
                        'daily_hours' => $this->calculateDailyHours($schedules),
                    ]);
                }
            }
            
            // Atualizar sequence para maior que o ID inserido
            DB::statement("SELECT setval('work_shift_templates_id_seq', GREATEST((SELECT MAX(id) FROM work_shift_templates), {$id}))");
            
            return $template;
        });
    }

    /**
     * Extrai horários de entrada e saída da descrição da jornada
     * 
     * @param string $description Descrição da jornada (ex: "SAÚDE -07:30-11:30 E 13:00-17:00")
     * @return array Array com entry_1, exit_1, entry_2, exit_2 (HH:MM)
     */
    protected function parseScheduleFromDescription(string $description): array
    {
        $schedules = [];
        
        // Remover prefixos (SEC, SAÚDE, NOVO, etc)
        $cleaned = preg_replace('/^[A-ZÇÃÁÀÂÊÉÍÓÔÕÚÜ\s]+-\s*/', '', $description);
        $cleaned = trim($cleaned);
        
        // CRÍTICO: Normalizar todos os formatos para HH:MM
        // Converter "8" -> "08:00"
        // Converter "13" -> "13:00"
        // Manter "11:30" -> "11:30"
        $cleaned = preg_replace_callback('/\b(\d{1,2})(?::(\d{2}))?\b/', function($m) {
            $hour = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $minute = isset($m[2]) ? $m[2] : '00';
            return $hour . ':' . $minute;
        }, $cleaned);
        
        Log::debug("Horário normalizado: {$cleaned}");
        
        // Padrão 1: HH:MM-HH:MM E HH:MM-HH:MM (dois períodos com E maiúsculo)
        if (preg_match('/(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})\s+E\s+(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})/i', $cleaned, $m)) {
            $schedules['entry_1'] = $m[1] . ':' . $m[2];
            $schedules['exit_1'] = $m[3] . ':' . $m[4];
            $schedules['entry_2'] = $m[5] . ':' . $m[6];
            $schedules['exit_2'] = $m[7] . ':' . $m[8];
            Log::debug("Match padrão 1 (E): entry_1={$schedules['entry_1']}, exit_1={$schedules['exit_1']}, entry_2={$schedules['entry_2']}, exit_2={$schedules['exit_2']}");
        }
        // Padrão 2: HH:MM-HH:MM - HH:MM-HH:MM (hífen duplo como separador)
        elseif (preg_match('/(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})/', $cleaned, $m)) {
            $schedules['entry_1'] = $m[1] . ':' . $m[2];
            $schedules['exit_1'] = $m[3] . ':' . $m[4];
            $schedules['entry_2'] = $m[5] . ':' . $m[6];
            $schedules['exit_2'] = $m[7] . ':' . $m[8];
            Log::debug("Match padrão 2 (hífen duplo): entry_1={$schedules['entry_1']}, exit_1={$schedules['exit_1']}, entry_2={$schedules['entry_2']}, exit_2={$schedules['exit_2']}");
        }
        // Padrão 3: Apenas um período HH:MM-HH:MM
        elseif (preg_match('/(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})/', $cleaned, $m)) {
            $schedules['entry_1'] = $m[1] . ':' . $m[2];
            $schedules['exit_1'] = $m[3] . ':' . $m[4];
            Log::debug("Match padrão 3 (período único): entry_1={$schedules['entry_1']}, exit_1={$schedules['exit_1']}");
        }
        else {
            Log::warning("Nenhum padrão de horário reconhecido em: {$cleaned}");
        }
        
        return $schedules;
    }

    /**
     * Gera um nome descritivo para o template baseado na descrição e horários
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
        if (isset($schedules['entry_1']) && isset($schedules['exit_1'])) {
            $name = "{$dept} - {$schedules['entry_1']} às {$schedules['exit_1']}";
            
            if (isset($schedules['entry_2']) && isset($schedules['exit_2'])) {
                $name .= " | {$schedules['entry_2']} às {$schedules['exit_2']}";
            }
        } else {
            // Se não conseguiu extrair horários, usar apenas o departamento e ID
            $name = "{$dept} - Jornada {$id}";
        }
        
        // Limitar tamanho do nome (max 255 caracteres)
        return substr($name, 0, 255);
    }

    /**
     * Calcula a carga horária semanal baseada nos horários de entrada/saída
     */
    protected function calculateWeeklyHours(array $schedules): float
    {
        if (empty($schedules)) {
            return 40.0; // Padrão: 40h semanais
        }
        
        $dailyHours = $this->calculateDailyHours($schedules);
        
        // Assumir 5 dias de trabalho por semana (segunda a sexta)
        return $dailyHours * 5;
    }

    /**
     * Calcula as horas diárias de trabalho
     */
    protected function calculateDailyHours(array $schedules): float
    {
        $totalMinutes = 0;
        
        // Período da manhã/primeiro turno
        if (isset($schedules['entry_1']) && isset($schedules['exit_1'])) {
            $entry1 = \Carbon\Carbon::createFromFormat('H:i', $schedules['entry_1']);
            $exit1 = \Carbon\Carbon::createFromFormat('H:i', $schedules['exit_1']);
            // CORRIGIDO: entry até exit (não exit até entry)
            $totalMinutes += $entry1->diffInMinutes($exit1);
        }
        
        // Período da tarde/segundo turno
        if (isset($schedules['entry_2']) && isset($schedules['exit_2'])) {
            $entry2 = \Carbon\Carbon::createFromFormat('H:i', $schedules['entry_2']);
            $exit2 = \Carbon\Carbon::createFromFormat('H:i', $schedules['exit_2']);
            // CORRIGIDO: entry até exit (não exit até entry)
            $totalMinutes += $entry2->diffInMinutes($exit2);
        }
        
        return round($totalMinutes / 60, 2);
    }
}
