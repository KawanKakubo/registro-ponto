<?php

namespace App\Jobs;

use App\Models\Department;
use App\Models\EmployeeRegistration;
use App\Models\Establishment;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCollaboratorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $userId;

    public function __construct(string $filePath, int $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        Log::info("========== INICIANDO IMPORTAÇÃO DE COLABORADORES ==========");
        Log::info("Arquivo: {$this->filePath}");

        $results = [
            'pessoas_criadas' => 0,
            'vinculos_criados' => 0,
            'erros' => 0,
            'detalhes_erros' => []
        ];

        DB::beginTransaction();

        try {
            $file = fopen($this->filePath, 'r');
            if (!$file) {
                throw new \Exception("Não foi possível abrir o arquivo: {$this->filePath}");
            }

            // Ler cabeçalho
            $header = fgetcsv($file);
            Log::info("Cabeçalho detectado: " . json_encode($header));

            $lineNumber = 1;
            while (($row = fgetcsv($file)) !== false) {
                $lineNumber++;
                
                try {
                    $data = array_combine($header, $row);
                    
                    // Validar campos obrigatórios
                    if (empty($data['pis_pasep'])) {
                        Log::warning("Linha {$lineNumber}: PIS/PASEP vazio, pulando");
                        $results['erros']++;
                        $results['detalhes_erros'][] = "Linha {$lineNumber}: PIS/PASEP vazio";
                        continue;
                    }

                    $pis = $this->cleanPis($data['pis_pasep']);
                    $nome = trim($data['full_name'] ?? '');
                    $cpf = $this->cleanCpf($data['cpf'] ?? null);
                    $matricula = trim($data['matricula'] ?? '');
                    $establishmentId = (int)($data['establishment_id'] ?? 1);
                    $departmentId = !empty($data['department_id']) ? (int)$data['department_id'] : null;
                    $admissionDate = !empty($data['admission_date']) ? Carbon::parse($data['admission_date']) : null;
                    $position = trim($data['role'] ?? ''); // CSV usa 'role' mas banco usa 'position'

                    // Criar pessoa
                    $person = Person::updateOrCreate(
                        ['pis_pasep' => $pis],
                        [
                            'full_name' => $nome,
                            'cpf' => $cpf
                        ]
                    );

                    if ($person->wasRecentlyCreated) {
                        $results['pessoas_criadas']++;
                        Log::debug("Linha {$lineNumber}: Pessoa CRIADA - PIS: {$pis}, Nome: {$nome}");
                    } else {
                        Log::debug("Linha {$lineNumber}: Pessoa JÁ EXISTE - PIS: {$pis}");
                    }

                    // Criar vínculo se tiver matrícula
                    if (!empty($matricula)) {
                        $registration = EmployeeRegistration::updateOrCreate(
                            ['matricula' => $matricula],
                            [
                                'person_id' => $person->id,
                                'establishment_id' => $establishmentId,
                                'department_id' => $departmentId,
                                'admission_date' => $admissionDate,
                                'position' => $position, // CORRIGIDO: usar 'position' do banco
                                'status' => 'active'
                            ]
                        );

                        if ($registration->wasRecentlyCreated) {
                            $results['vinculos_criados']++;
                            Log::debug("Linha {$lineNumber}: Vínculo CRIADO - Matrícula: {$matricula}");
                        } else {
                            Log::debug("Linha {$lineNumber}: Vínculo ATUALIZADO - Matrícula: {$matricula}");
                        }
                    }

                } catch (\Exception $e) {
                    $results['erros']++;
                    $erro = "Linha {$lineNumber}: {$e->getMessage()}";
                    $results['detalhes_erros'][] = $erro;
                    Log::error($erro);
                }
            }

            fclose($file);

            DB::commit();

            Log::info("========== IMPORTAÇÃO DE COLABORADORES CONCLUÍDA ==========");
            Log::info("Pessoas criadas: {$results['pessoas_criadas']}");
            Log::info("Vínculos criados: {$results['vinculos_criados']}");
            Log::info("Erros: {$results['erros']}");
            
            if (!empty($results['detalhes_erros'])) {
                Log::warning("Detalhes dos erros:");
                foreach ($results['detalhes_erros'] as $erro) {
                    Log::warning("  - {$erro}");
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERRO CRÍTICO na importação de colaboradores: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    protected function cleanPis(string $pis): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $pis);
        if (strlen($cleaned) > 11) {
            Log::warning("PIS com mais de 11 dígitos detectado: {$pis}, usando apenas os primeiros 11 dígitos");
            $cleaned = substr($cleaned, 0, 11);
        }
        return $cleaned;
    }

    protected function cleanCpf(?string $cpf): ?string
    {
        if (empty($cpf)) {
            return null;
        }
        
        $cleaned = preg_replace('/[^0-9]/', '', $cpf);
        return empty($cleaned) ? null : $cleaned;
    }
}
