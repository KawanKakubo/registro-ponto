<?php
/**
 * Script FINAL - CSV Dinâmico como base principal
 * 
 * O CSV dinâmico tem os funcionários corretos por departamento com CPF
 * O CSV de listagem complementa com cargo e dados adicionais
 */

$csvDinamicoPath = __DIR__ . '/funcionarios_dinamica.csv';
$csvListagemPath = __DIR__ . '/../LISTAGEM DE FUNCIONÁRIOS.csv';

// Mapeamento de departamentos - IDs CORRETOS conforme DepartmentSeeder
// 1 = ADMINISTRAÇÃO E RH
// 2 = AGRICULTURA E ABASTECIMENTO
// 3 = AGRICULTURA, ABASTECIMENTO E MEIO AMBIENTE
// 4 = ASSISTÊNCIA SOCIAL
// 5 = CIÊNCIA, TECNOLOGIA E INOVAÇÃO (SECTI)
// 6 = CULTURA E TURISMO
// 7 = DEFESA CIVIL
// 8 = EDUCAÇÃO
// 9 = ESPORTE E LAZER
// 10 = FINANÇAS
// 11 = GABINETE DO PREFEITO
// 12 = NENHUM
// 13 = OBRAS E SERVIÇOS URBANOS
// 14 = PROCURADORIA MUNICIPAL
// 15 = PÓLO UAB
// 16 = SAÚDE
// 17 = SEGURANÇA ALIMENTAR E NUTRICIONAL
// 18 = TRABALHO E GERAÇÃO DE EMPREGOS
$departamentosMap = [
    // GABINETE DO PREFEITO (ID 11)
    'GABINETE DO PREFEITO' => 11,
    'GABINETE' => 11,
    
    // FINANÇAS (ID 10)
    'SECRETARIA MUNICIPAL DE FINANCAS' => 10,
    'FINANCAS' => 10,
    'FINANÇAS' => 10,
    
    // ADMINISTRAÇÃO E RH (ID 1)
    'SEC MUN DE ADM. E RECURSOS HUMANOS' => 1,
    'ADMINISTRACAO' => 1,
    'ADMINISTRAÇÃO' => 1,
    'ADMINISTRAÇÃO E RH' => 1,
    
    // EDUCAÇÃO (ID 8)
    'SECRETARIA MUNICIPAL DE EDUCACAO' => 8,
    'EDUCACAO' => 8,
    'EDUCAÇÃO' => 8,
    
    // SAÚDE (ID 16)
    'SECRETARIA MUNICIPAL DE SAUDE' => 16,
    'SAUDE' => 16,
    'SAÚDE' => 16,
    
    // ASSISTÊNCIA SOCIAL (ID 4)
    'SEC MUNICIPAL DE ASSISTENCIA SOCIAL' => 4,
    'ASSISTENCIA SOCIAL' => 4,
    'ASSISTÊNCIA SOCIAL' => 4,
    
    // ESPORTE E LAZER (ID 9)
    'SEC MUN DE ESPORTE E LAZER' => 9,
    'ESPORTE E LAZER' => 9,
    'ESPORTE' => 9,
    
    // AGRICULTURA E ABASTECIMENTO (ID 2)
    'SEC MUN DE AGRICULTURA E ABASTECIMENTO' => 2,
    'AGRICULTURA' => 2,
    'AGRICULTURA E ABASTECIMENTO' => 2,
    
    // AGRICULTURA, ABASTECIMENTO E MEIO AMBIENTE (ID 3)
    'AGRICULTURA, ABASTECIMENTO E MEIO AMBIENTE' => 3,
    
    // DESENVOLVIMENTO LOCAL -> NENHUM (ID 12)
    'SECRETARIA MUN DE DESENVOLVIMENTO LOCAL' => 12,
    'DESENVOLVIMENTO' => 12,
    'DESENVOLVIMENTO LOCAL' => 12,
    
    // OBRAS E SERVIÇOS URBANOS (ID 13)
    'SEC MUN DE OBRAS E SERVICOS URBANOS' => 13,
    'OBRAS E SERVICOS URBANOS' => 13,
    'OBRAS E SERVIÇOS URBANOS' => 13,
    'OBRAS' => 13,
    
    // TRABALHO E GERAÇÃO DE EMPREGOS (ID 18)
    'SEC MUN DO TRAB E GERACAO DE EMPREGOS' => 18,
    'TRABALHO' => 18,
    'TRABALHO E GERAÇÃO DE EMPREGOS' => 18,
    
    // CULTURA E TURISMO (ID 6)
    'SEC MUN DE CULTURA E TURISMO' => 6,
    'CULTURA E TURISMO' => 6,
    'CULTURA' => 6,
    
    // PROCURADORIA MUNICIPAL (ID 14)
    'PROCURADORIA GERAL DO MUNICIPIO' => 14,
    'PROCURADORIA' => 14,
    'PROCURADORIA MUNICIPAL' => 14,
    
    // CIÊNCIA, TECNOLOGIA E INOVAÇÃO / SECTI (ID 5)
    'SECRETARIA MUN DE CIÊNCIA, TECNOLOGIA E INOVAÇÃO' => 5,
    'CIÊNCIA, TECNOLOGIA E INOVAÇÃO' => 5,
    'CIENCIA' => 5,
    'TECNOLOGIA' => 5,
    'SECTI' => 5,
    
    // SEGURANÇA ALIMENTAR E NUTRICIONAL (ID 17)
    'SECRETARIA MUN SEGURANÇA ALIMENTAR E NUTRICIONAL' => 17,
    'SEGURANCA ALIMENTAR' => 17,
    'SEGURANÇA ALIMENTAR' => 17,
    'SEGURANÇA ALIMENTAR E NUTRICIONAL' => 17,
    
    // DEFESA CIVIL (ID 7)
    'DEFESA CIVIL' => 7,
    
    // PÓLO UAB (ID 15)
    'PÓLO UAB' => 15,
    'POLO UAB' => 15,
    
    // NENHUM (ID 12)
    'NENHUM' => 12,
];

function cleanCpf($cpf) {
    return preg_replace('/[^0-9]/', '', $cpf);
}

function cleanPis($pis) {
    $pis = preg_replace('/[^0-9]/', '', $pis);
    if (strlen($pis) > 11) {
        $pis = substr($pis, -11);
    }
    return $pis;
}

function parseDate($date) {
    if (empty($date)) return null;
    $date = trim($date);
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
    }
    return null;
}

function normalizeName($name) {
    $name = mb_strtoupper(trim($name), 'UTF-8');
    $name = preg_replace('/\s+/', ' ', $name);
    return $name;
}

function getDepartmentId($deptName, $map) {
    $deptName = mb_strtoupper(trim($deptName), 'UTF-8');
    
    foreach ($map as $key => $id) {
        if (mb_strtoupper($key, 'UTF-8') === $deptName) {
            return $id;
        }
    }
    
    foreach ($map as $key => $id) {
        if (stripos($deptName, $key) !== false || stripos($key, $deptName) !== false) {
            return $id;
        }
    }
    
    return 8; // Default: EDUCAÇÃO (ID 8)
}

echo "=== PROCESSAMENTO FINAL - CSV DINÂMICO COMO BASE ===\n\n";

// ========================================
// PASSO 1: Ler CSV Dinâmico (BASE PRINCIPAL - tem CPF e dept correto)
// ========================================
$funcionariosDinamico = [];
$pisDinamico = [];
$matriculasDinamico = [];
$pisUsadosDinamico = []; // NOVO: Para deduplicar por PIS
$currentDepartmentId = null;

$lines = file($csvDinamicoPath, FILE_IGNORE_NEW_LINES);
foreach ($lines as $line) {
    if (preg_match('/DEPARTAMENTO[:\s-]+(.+?)(?:,|$)/i', $line, $matches)) {
        $deptName = trim(str_replace(['"', "'"], '', $matches[1]));
        $currentDepartmentId = getDepartmentId($deptName, $departamentosMap);
        continue;
    }
    
    if (strpos($line, 'Cód. Matrícula') !== false) continue;
    
    $cols = str_getcsv($line);
    if (count($cols) >= 10 && $currentDepartmentId !== null) {
        $matricula = trim($cols[1] ?? '');
        $nome = trim($cols[6] ?? '');
        $cpf = cleanCpf($cols[8] ?? '');
        $pis = cleanPis($cols[9] ?? '');
        $dataAdmissao = parseDate($cols[10] ?? '');
        
        $matricula = preg_replace('/\/\d+$/', '', $matricula);
        
        if (!empty($nome) && !empty($matricula)) {
            $key = $matricula;
            
            // NOVO: Se o PIS já foi usado, pula (mesma pessoa com múltiplos vínculos)
            if (!empty($pis) && isset($pisUsadosDinamico[$pis])) {
                continue; // Mesma pessoa com outro vínculo - ignora
            }
            
            // Se já existe com essa matrícula, não duplicar
            if (!isset($funcionariosDinamico[$key])) {
                $funcionariosDinamico[$key] = [
                    'matricula' => $matricula,
                    'nome' => $nome,
                    'cpf' => $cpf,
                    'pis' => $pis,
                    'data_admissao' => $dataAdmissao,
                    'departamento_id' => $currentDepartmentId,
                    'cargo' => 'SERVIDOR PÚBLICO',
                ];
                
                if (!empty($pis)) {
                    $pisDinamico[$pis] = $key;
                    $pisUsadosDinamico[$pis] = true; // NOVO: Marca PIS como usado
                }
                $matriculasDinamico[$matricula] = true;
            }
        }
    }
}

echo "CSV Dinâmico: " . count($funcionariosDinamico) . " funcionários únicos (dedup por PIS)\n";

// ========================================
// PASSO 2: Ler CSV Listagem para obter cargos e dados complementares
// ========================================
$dadosListagem = [];
$content = file_get_contents($csvListagemPath);
$content = mb_convert_encoding($content, 'UTF-8', 'auto');
$lines = explode("\n", $content);

$isFirstLine = true;
foreach ($lines as $line) {
    if ($isFirstLine) {
        $isFirstLine = false;
        continue;
    }
    
    $line = trim($line);
    if (empty($line)) continue;
    
    $cols = str_getcsv($line, ',');
    if (count($cols) < 8) continue;
    
    $nFolha = trim($cols[0] ?? '');
    $pis = cleanPis($cols[1] ?? '');
    $nome = trim($cols[2] ?? '');
    $matricula = trim($cols[3] ?? '');
    $cargo = trim($cols[5] ?? '');
    $departamento = trim($cols[6] ?? '');
    $dataAdmissao = parseDate($cols[7] ?? '');
    
    if (empty($matricula)) {
        $matricula = $nFolha;
    }
    
    if (!empty($pis)) {
        $dadosListagem[$pis] = [
            'matricula' => $matricula,
            'nome' => $nome,
            'pis' => $pis,
            'cargo' => $cargo,
            'departamento' => $departamento,
            'data_admissao' => $dataAdmissao,
        ];
    }
}

echo "CSV Listagem: " . count($dadosListagem) . " funcionários (por PIS)\n";

// ========================================
// PASSO 3: Complementar dados do dinâmico com listagem
// ========================================
foreach ($funcionariosDinamico as $key => &$func) {
    $pis = $func['pis'];
    if (!empty($pis) && isset($dadosListagem[$pis])) {
        $listagem = $dadosListagem[$pis];
        if (!empty($listagem['cargo'])) {
            $func['cargo'] = $listagem['cargo'];
        }
        if (empty($func['data_admissao']) && !empty($listagem['data_admissao'])) {
            $func['data_admissao'] = $listagem['data_admissao'];
        }
        // Usar nome da Listagem se for mais completo (mais longo)
        if (!empty($listagem['nome']) && strlen($listagem['nome']) > strlen($func['nome'])) {
            $func['nome'] = $listagem['nome'];
        }
    }
}
unset($func);

// ========================================
// PASSO 4: Adicionar funcionários da listagem que não estão no dinâmico
// ========================================
$adicionadosDaListagem = 0;
foreach ($dadosListagem as $pis => $listagem) {
    // Se não existe no dinâmico por PIS
    if (!isset($pisDinamico[$pis])) {
        $matricula = $listagem['matricula'];
        
        // E também não existe por matrícula
        if (!isset($matriculasDinamico[$matricula])) {
            $deptId = getDepartmentId($listagem['departamento'], $departamentosMap);
            
            $funcionariosDinamico[$matricula] = [
                'matricula' => $matricula,
                'nome' => $listagem['nome'],
                'cpf' => null,
                'pis' => $pis,
                'data_admissao' => $listagem['data_admissao'] ?: '2020-01-01',
                'departamento_id' => $deptId,
                'cargo' => $listagem['cargo'] ?: 'SERVIDOR PÚBLICO',
            ];
            
            $adicionadosDaListagem++;
        }
    }
}

echo "Adicionados da Listagem: $adicionadosDaListagem funcionários\n";
echo "\nTOTAL FINAL: " . count($funcionariosDinamico) . " funcionários\n";

// ========================================
// PASSO 5: Remover CPFs duplicados
// ========================================
$cpfsUsados = [];
$cpfsDuplicados = 0;
foreach ($funcionariosDinamico as $key => &$func) {
    $cpf = $func['cpf'];
    if (!empty($cpf)) {
        if (isset($cpfsUsados[$cpf])) {
            $func['cpf'] = null;
            $cpfsDuplicados++;
        } else {
            $cpfsUsados[$cpf] = $key;
        }
    }
}
unset($func);

echo "CPFs únicos: " . count($cpfsUsados) . "\n";
echo "CPFs duplicados removidos: $cpfsDuplicados\n";

// ========================================
// PASSO 6: Gerar arquivos de saída
// ========================================
$outputDir = __DIR__ . '/seeder_output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$peopleOutput = "<?php\n\n    private function getPeopleData(): array\n    {\n        return [\n";
$registrationsOutput = "<?php\n\n    private function getEmployeeRegistrationsData(): array\n    {\n        return [\n";

$id = 1;
foreach ($funcionariosDinamico as $func) {
    $nome = str_replace("'", "\\'", $func['nome']);
    $cpf = !empty($func['cpf']) ? "'{$func['cpf']}'" : 'null';
    $pis = !empty($func['pis']) ? "'{$func['pis']}'" : 'null';
    $cargo = str_replace("'", "\\'", $func['cargo']);
    $dataAdmissao = $func['data_admissao'] ?: '2020-01-01';
    $departamentoId = $func['departamento_id'];
    $matricula = $func['matricula'];
    
    $peopleOutput .= "            ['id' => {$id}, 'full_name' => '{$nome}', 'cpf' => {$cpf}, 'pis_pasep' => {$pis}, 'ctps' => null],\n";
    $registrationsOutput .= "            ['id' => {$id}, 'person_id' => {$id}, 'establishment_id' => 1, 'department_id' => {$departamentoId}, 'matricula' => '{$matricula}', 'status' => 'active', 'admission_date' => '{$dataAdmissao}', 'position' => '{$cargo}'],\n";
    
    $id++;
}

$peopleOutput .= "        ];\n    }";
$registrationsOutput .= "        ];\n    }";

file_put_contents($outputDir . '/people_data_final.php', $peopleOutput);
file_put_contents($outputDir . '/registrations_data_final.php', $registrationsOutput);

echo "\n=== ARQUIVOS GERADOS ===\n";
echo "Total de registros: " . ($id - 1) . "\n";

// ========================================
// ESTATÍSTICAS POR DEPARTAMENTO
// ========================================
echo "\n=== FUNCIONÁRIOS POR DEPARTAMENTO ===\n";
$deptStats = [];
foreach ($funcionariosDinamico as $func) {
    $deptId = $func['departamento_id'];
    if (!isset($deptStats[$deptId])) {
        $deptStats[$deptId] = 0;
    }
    $deptStats[$deptId]++;
}
ksort($deptStats);

$deptNames = [
    1 => 'ADMINISTRAÇÃO E RH',
    2 => 'AGRICULTURA E ABASTECIMENTO',
    3 => 'AGRICULTURA E MEIO AMBIENTE',
    4 => 'ASSISTÊNCIA SOCIAL',
    5 => 'SECTI',
    6 => 'CULTURA E TURISMO',
    7 => 'DEFESA CIVIL',
    8 => 'EDUCAÇÃO',
    9 => 'ESPORTE E LAZER',
    10 => 'FINANÇAS',
    11 => 'GABINETE DO PREFEITO',
    12 => 'NENHUM',
    13 => 'OBRAS E SERVIÇOS URBANOS',
    14 => 'PROCURADORIA MUNICIPAL',
    15 => 'PÓLO UAB',
    16 => 'SAÚDE',
    17 => 'SEG. ALIMENTAR E NUTRICIONAL',
    18 => 'TRABALHO E GERAÇÃO DE EMPREGOS',
];

$total = 0;
foreach ($deptStats as $deptId => $count) {
    $name = $deptNames[$deptId] ?? "Dept $deptId";
    printf("  %2d. %-30s %4d\n", $deptId, $name, $count);
    $total += $count;
}
echo "  " . str_repeat('-', 40) . "\n";
echo "      TOTAL:                          $total\n";
