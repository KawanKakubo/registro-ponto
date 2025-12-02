<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\EmployeeRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeesFromCsvSeeder extends Seeder
{
    /**
     * Importa colaboradores e vínculos do arquivo CSV
     * 
     * Estrutura esperada do CSV:
     * full_name,cpf,pis_pasep,matricula,establishment_id,department_id,admission_date,role
     */
    public function run(): void
    {
        $csvPath = base_path('importacao-colaboradores.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("❌ Arquivo não encontrado: {$csvPath}");
            $this->command->info("📝 Coloque o arquivo 'importacao-colaboradores.csv' na raiz do projeto");
            return;
        }

        $this->command->info("📂 Lendo arquivo CSV...");
        
        $handle = fopen($csvPath, 'r');
        
        // Pular cabeçalho
        $header = fgetcsv($handle, 1000, ',');
        
        $stats = [
            'pessoas_criadas' => 0,
            'pessoas_existentes' => 0,
            'vinculos_criados' => 0,
            'vinculos_atualizados' => 0,
            'erros' => 0,
            'linhas_processadas' => 0,
        ];
        
        $errors = [];
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $stats['linhas_processadas']++;
            
            // Processar cada linha em sua própria transação
            DB::beginTransaction();
            
            try {
                    // Mapear dados do CSV
                    $rowData = [
                        'full_name' => $data[0] ?? null,
                        'cpf' => $this->cleanCpf($data[1] ?? ''),
                        'pis_pasep' => $this->cleanPis($data[2] ?? ''),
                        'matricula' => trim($data[3] ?? ''),
                        'establishment_id' => (int)($data[4] ?? 0),
                        'department_id' => !empty($data[5]) ? (int)$data[5] : null,
                        'admission_date' => $data[6] ?? null,
                        'position' => $data[7] ?? null,
                    ];
                    
                    // Validar dados mínimos
                    if (empty($rowData['full_name']) || empty($rowData['cpf'])) {
                        $errors[] = "Linha {$stats['linhas_processadas']}: Nome ou CPF vazio";
                        $stats['erros']++;
                        continue;
                    }
                    
                    if ($rowData['establishment_id'] <= 0) {
                        $errors[] = "Linha {$stats['linhas_processadas']}: ID do estabelecimento inválido";
                        $stats['erros']++;
                        continue;
                    }
                    
                    // Validar se o departamento existe (se fornecido)
                    if ($rowData['department_id'] !== null) {
                        $departmentExists = \App\Models\Department::where('id', $rowData['department_id'])->exists();
                        if (!$departmentExists) {
                            // Departamento não existe - setar como null e registrar warning
                            $this->command->warn("⚠️  Linha {$stats['linhas_processadas']}: Departamento {$rowData['department_id']} não existe - será definido como NULL");
                            $rowData['department_id'] = null;
                        }
                    }
                    
                    // Buscar ou criar PESSOA
                    // Primeiro tenta pelo CPF, depois pelo PIS
                    $person = Person::where('cpf', $rowData['cpf'])->first();
                    
                    if (!$person && !empty($rowData['pis_pasep'])) {
                        $person = Person::where('pis_pasep', $rowData['pis_pasep'])->first();
                    }
                    
                    if (!$person) {
                        // Criar nova pessoa
                        $person = Person::create([
                            'full_name' => $rowData['full_name'],
                            'cpf' => $rowData['cpf'],
                            'pis_pasep' => $rowData['pis_pasep'],
                        ]);
                        $stats['pessoas_criadas']++;
                        
                        $this->command->info("✅ Pessoa criada: {$person->full_name} (CPF: {$rowData['cpf']})");
                    } else {
                        $stats['pessoas_existentes']++;
                        
                        // Atualizar PIS se não existir
                        if (empty($person->pis_pasep) && !empty($rowData['pis_pasep'])) {
                            $person->update(['pis_pasep' => $rowData['pis_pasep']]);
                        }
                    }
                    
                    // Buscar ou criar VÍNCULO
                    $registration = EmployeeRegistration::where('matricula', $rowData['matricula'])->first();
                    
                    if (!$registration) {
                        // Criar novo vínculo
                        $registration = EmployeeRegistration::create([
                            'person_id' => $person->id,
                            'matricula' => $rowData['matricula'],
                            'establishment_id' => $rowData['establishment_id'],
                            'department_id' => $rowData['department_id'],
                            'admission_date' => $rowData['admission_date'],
                            'position' => $rowData['position'],
                            'status' => 'active',
                        ]);
                        $stats['vinculos_criados']++;
                        
                        $this->command->comment("   └─ Vínculo criado: Matrícula {$rowData['matricula']} - {$rowData['position']}");
                    } else {
                        // Atualizar vínculo existente
                        $registration->update([
                            'person_id' => $person->id,
                            'establishment_id' => $rowData['establishment_id'],
                            'department_id' => $rowData['department_id'] ?: $registration->department_id,
                            'admission_date' => $rowData['admission_date'],
                            'position' => $rowData['position'] ?: $registration->position,
                            'status' => 'active',
                        ]);
                        $stats['vinculos_atualizados']++;
                        
                        $this->command->comment("   └─ Vínculo atualizado: Matrícula {$rowData['matricula']}");
                    }
                    
                    DB::commit();
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $stats['erros']++;
                    $errors[] = "Linha {$stats['linhas_processadas']}: {$e->getMessage()}";
                    Log::error("Erro ao processar linha {$stats['linhas_processadas']}: " . $e->getMessage());
                }
            }
            
            fclose($handle);
            
            // Exibir estatísticas
            $this->command->newLine();
            $this->command->info("═══════════════════════════════════════════════");
            $this->command->info("📊 ESTATÍSTICAS DA IMPORTAÇÃO");
            $this->command->info("═══════════════════════════════════════════════");
            $this->command->table(
                ['Métrica', 'Quantidade'],
                [
                    ['Linhas processadas', $stats['linhas_processadas']],
                    ['Pessoas criadas', $stats['pessoas_criadas']],
                    ['Pessoas já existentes', $stats['pessoas_existentes']],
                    ['Vínculos criados', $stats['vinculos_criados']],
                    ['Vínculos atualizados', $stats['vinculos_atualizados']],
                    ['Erros', $stats['erros']],
                ]
            );
            
            if (count($errors) > 0) {
                $this->command->newLine();
                $this->command->warn("⚠️  ERROS ENCONTRADOS:");
                foreach (array_slice($errors, 0, 10) as $error) {
                    $this->command->error("  • {$error}");
                }
                if (count($errors) > 10) {
                    $this->command->warn("  ... e mais " . (count($errors) - 10) . " erro(s)");
                }
            }
            
            $this->command->newLine();
            $this->command->info("✅ Importação concluída com sucesso!");
    }
    
    /**
     * Limpa e normaliza CPF
     */
    private function cleanCpf(string $cpf): string
    {
        // Remove pontos, traços e espaços
        $cleaned = preg_replace('/[^0-9]/', '', trim($cpf));
        
        // Se tiver menos de 11 dígitos, preencher com zeros à esquerda
        if (strlen($cleaned) < 11 && strlen($cleaned) > 0) {
            $cleaned = str_pad($cleaned, 11, '0', STR_PAD_LEFT);
        }
        
        return $cleaned;
    }
    
    /**
     * Limpa e normaliza PIS/PASEP
     */
    private function cleanPis(string $pis): string
    {
        // Remove pontos, traços e espaços
        $cleaned = preg_replace('/[^0-9]/', '', trim($pis));
        
        // Se tiver menos de 11 dígitos, preencher com zeros à esquerda
        if (strlen($cleaned) < 11 && strlen($cleaned) > 0) {
            $cleaned = str_pad($cleaned, 11, '0', STR_PAD_LEFT);
        }
        
        return $cleaned;
    }
}
