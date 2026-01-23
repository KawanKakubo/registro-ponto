<?php
/**
 * Script COMPLETO para combinar os dois CSVs e gerar dados finais
 * - CSV Dinâmico: 647 funcionários com CPF e departamento correto
 * - CSV Listagem: 961 funcionários com cargo e PIS
 * 
 * Estratégia:
 * 1. Usar CSV dinâmico como fonte principal (tem CPF e departamento correto)
 * 2. Adicionar funcionários da listagem que não estão no dinâmico
 * 3. Completar dados usando matrícula como chave
 */

$csvDinamicoPath = __DIR__ . '/funcionarios_dinamica.csv';
$csvListagemPath = __DIR__ . '/../LISTAGEM DE FUNCIONÁRIOS.csv';

// Mapeamento de departamentos do CSV dinâmico para IDs
$departamentosMapDinamico = [
    'GABINETE DO PREFEITO' => 1,
    'SECRETARIA MUNICIPAL DE FINANCAS' => 2,
    'SEC MUN DE ADM. E RECURSOS HUMANOS' => 3,
    'SECRETARIA MUNICIPAL DE EDUCACAO' => 4,
    'SECRETARIA MUNICIPAL DE SAUDE' => 5,
    'SEC MUNICIPAL DE ASSISTENCIA SOCIAL' => 6,
    'SEC MUN DE ESPORTE E LAZER' => 7,
    'SEC MUN DE AGRICULTURA E ABASTECIMENTO' => 8,
    'SECRETARIA MUN DE DESENVOLVIMENTO LOCAL' => 9,
    'SEC MUN DE OBRAS E SERVICOS URBANOS' => 10,
    'SEC MUN DO TRAB E GERACAO DE EMPREGOS' => 11,
    'SEC MUN DE CULTURA E TURISMO' => 12,
    'PROCURADORIA GERAL DO MUNICIPIO' => 13,
    'SECRETARIA MUN DE CIÊNCIA, TECNOLOGIA E INOVAÇÃO' => 14,
    'SECRETARIA MUN SEGURANÇA ALIMENTAR E NUTRICIONAL' => 15,
];

// Mapeamento de departamentos do CSV listagem
$departamentosMapListagem = [
    'GABINETE DO PREFEITO' => 1,
    'SECRETARIA DE FINANÇAS' => 2,
    'FINANÇAS' => 2,
    'SECRETARIA DE ADMINISTRAÇÃO E RECURSOS HUMANOS' => 3,
    'ADMINISTRAÇÃO E RECURSOS HUMANOS' => 3,
    'RECURSOS HUMANOS' => 3,
    'SECRETARIA DE EDUCAÇÃO' => 4,
    'EDUCAÇÃO' => 4,
    'SECRETARIA DE SAÚDE' => 5,
    'SAÚDE' => 5,
    'SECRETARIA DE ASSISTÊNCIA SOCIAL' => 6,
    'ASSISTÊNCIA SOCIAL' => 6,
    'SECRETARIA DE ESPORTE E LAZER' => 7,
    'ESPORTE E LAZER' => 7,
    'SECRETARIA DE AGRICULTURA E ABASTECIMENTO' => 8,
    'AGRICULTURA E ABASTECIMENTO' => 8,
    'AGRICULTURA' => 8,
    'SECRETARIA DE DESENVOLVIMENTO LOCAL' => 9,
    'DESENVOLVIMENTO LOCAL' => 9,
    'SECRETARIA DE OBRAS E SERVIÇOS URBANOS' => 10,
    'OBRAS E SERVIÇOS URBANOS' => 10,
    'OBRAS' => 10,
    'SECRETARIA DO TRABALHO E GERAÇÃO DE EMPREGOS' => 11,
    'TRABALHO E GERAÇÃO DE EMPREGOS' => 11,
    'TRABALHO' => 11,
    'SECRETARIA DE CULTURA E TURISMO' => 12,
    'CULTURA E TURISMO' => 12,
    'CULTURA' => 12,
    'PROCURADORIA GERAL DO MUNICÍPIO' => 13,
    'PROCURADORIA' => 13,
    'SECRETARIA DE CIÊNCIA, TECNOLOGIA E INOVAÇÃO' => 14,
    'CIÊNCIA, TECNOLOGIA E INOVAÇÃO' => 14,
    'SECTI' => 14,
    'SECRETARIA DE SEGURANÇA ALIMENTAR E NUTRICIONAL' => 15,
    'SEGURANÇA ALIMENTAR' => 15,
    // Default departamentos da listagem original
    'SEC. MUN. DE EDUCAÇÃO' => 4,
    'SEC. DE EDUCAÇÃO' => 4,
    'SEC. MUN. DE SAÚDE' => 5,
    'SEC. DE SAÚDE' => 5,
    'SEC. DE ASSISTÊNCIA SOCIAL' => 6,
    'SEC. DE OBRAS' => 10,
    'SEC. DE AGRICULTURA' => 8,
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
    $parts = explode('/', trim($date));
    if (count($parts) === 3) {
        return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
    }
    return null;
}

function getDepartmentId($deptName, $mapDinamico, $mapListagem) {
    $deptName = trim($deptName);
    $deptNameUpper = mb_strtoupper($deptName, 'UTF-8');
    
    // Tentar match exato primeiro
    foreach ($mapListagem as $key => $id) {
        if (mb_strtoupper($key, 'UTF-8') === $deptNameUpper) {
            return $id;
        }
    }
    
    // Tentar match parcial
    foreach ($mapListagem as $key => $id) {
        if (stripos($deptNameUpper, mb_strtoupper($key, 'UTF-8')) !== false) {
            return $id;
        }
    }
    
    // Default: Educação (mais comum)
    return 4;
}

echo "=== PROCESSAMENTO COMPLETO DOS CSVs ===\n\n";

// ========================================
// PASSO 1: Ler CSV Dinâmico (tem CPF e departamento correto)
// ========================================
$funcionariosDinamico = [];
$currentDepartmentId = null;

$lines = file($csvDinamicoPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // Detectar linha de departamento
    if (preg_match('/DEPARTAMENTO[:\s-]+(.+?)(?:,|$)/i', $line, $matches)) {
        $deptName = trim($matches[1]);
        $deptName = str_replace(['"', "'"], '', $deptName);
        $deptName = trim($deptName);
        
        $currentDepartmentId = null;
        foreach ($departamentosMapDinamico as $key => $id) {
            if (stripos($deptName, $key) !== false || stripos($key, $deptName) !== false) {
                $currentDepartmentId = $id;
                break;
            }
        }
        
        if ($currentDepartmentId === null) {
            foreach ($departamentosMapDinamico as $key => $id) {
                $keyWords = explode(' ', $key);
                $matchCount = 0;
                foreach ($keyWords as $word) {
                    if (strlen($word) > 3 && stripos($deptName, $word) !== false) {
                        $matchCount++;
                    }
                }
                if ($matchCount >= 2) {
                    $currentDepartmentId = $id;
                    break;
                }
            }
        }
        continue;
    }
    
    if (strpos($line, 'Cód. Matrícula') !== false) continue;
    
    $cols = str_getcsv($line);
    if (count($cols) >= 10) {
        $matricula = trim($cols[1] ?? '');
        $nome = trim($cols[6] ?? '');
        $cpf = cleanCpf($cols[8] ?? '');
        $pis = cleanPis($cols[9] ?? '');
        $dataAdmissao = parseDate($cols[10] ?? '');
        
        $matricula = preg_replace('/\/\d+$/', '', $matricula);
        
        if (!empty($nome) && !empty($matricula) && $currentDepartmentId !== null) {
            $funcionariosDinamico[$matricula] = [
                'matricula' => $matricula,
                'nome' => $nome,
                'cpf' => $cpf,
                'pis' => $pis,
                'data_admissao' => $dataAdmissao,
                'departamento_id' => $currentDepartmentId,
                'cargo' => 'SERVIDOR PÚBLICO',
            ];
        }
    }
}

echo "CSV Dinâmico: " . count($funcionariosDinamico) . " funcionários\n";

// ========================================
// PASSO 2: Ler CSV Listagem (tem cargo e PIS)
// ========================================
$funcionariosListagem = [];
$listagemLines = file($csvListagemPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$header = null;

foreach ($listagemLines as $line) {
    $cols = str_getcsv($line, ';');
    if ($header === null) {
        $header = $cols;
        continue;
    }
    
    $matricula = trim($cols[0] ?? '');
    $pis = cleanPis($cols[1] ?? '');
    $nome = trim($cols[2] ?? '');
    $cargo = trim($cols[5] ?? '');
    $departamento = trim($cols[6] ?? '');
    $dataAdmissao = parseDate($cols[7] ?? '');
    
    if (!empty($matricula)) {
        $deptId = getDepartmentId($departamento, $departamentosMapDinamico, $departamentosMapListagem);
        
        $funcionariosListagem[$matricula] = [
            'matricula' => $matricula,
            'nome' => $nome,
            'pis' => $pis,
            'cargo' => $cargo ?: 'SERVIDOR PÚBLICO',
            'departamento_nome' => $departamento,
            'departamento_id' => $deptId,
            'data_admissao' => $dataAdmissao,
        ];
    }
}

echo "CSV Listagem: " . count($funcionariosListagem) . " funcionários\n";

// ========================================
// PASSO 3: Combinar dados - CSV Dinâmico tem prioridade
// ========================================
$funcionariosFinais = [];
$cpfsUsados = [];

// Primeiro: adicionar todos do CSV dinâmico (tem CPF e dept correto)
foreach ($funcionariosDinamico as $matricula => $func) {
    $cpf = $func['cpf'];
    
    // Completar com dados da listagem
    if (isset($funcionariosListagem[$matricula])) {
        $listagem = $funcionariosListagem[$matricula];
        $func['cargo'] = $listagem['cargo'] ?: $func['cargo'];
        if (empty($func['pis'])) {
            $func['pis'] = $listagem['pis'];
        }
        if (empty($func['data_admissao'])) {
            $func['data_admissao'] = $listagem['data_admissao'];
        }
    }
    
    // Verificar CPF duplicado
    if (!empty($cpf)) {
        if (isset($cpfsUsados[$cpf])) {
            $cpf = null;
        } else {
            $cpfsUsados[$cpf] = $matricula;
        }
    }
    
    $func['cpf'] = $cpf;
    $funcionariosFinais[$matricula] = $func;
}

echo "Após CSV Dinâmico: " . count($funcionariosFinais) . " funcionários\n";

// Segundo: adicionar funcionários da listagem que não estão no dinâmico
$adicionadosDaListagem = 0;
foreach ($funcionariosListagem as $matricula => $func) {
    if (!isset($funcionariosFinais[$matricula])) {
        $func['cpf'] = null; // Não tem CPF no CSV listagem
        $funcionariosFinais[$matricula] = $func;
        $adicionadosDaListagem++;
    }
}

echo "Adicionados da Listagem: $adicionadosDaListagem funcionários\n";
echo "TOTAL FINAL: " . count($funcionariosFinais) . " funcionários\n";
echo "CPFs únicos: " . count($cpfsUsados) . "\n";

// ========================================
// PASSO 4: Gerar arquivos de saída
// ========================================
$outputDir = __DIR__ . '/seeder_output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Ordenar por matrícula
ksort($funcionariosFinais);

$peopleOutput = "<?php\n\n    private function getPeopleData(): array\n    {\n        return [\n";
$registrationsOutput = "<?php\n\n    private function getEmployeeRegistrationsData(): array\n    {\n        return [\n";

$id = 1;
foreach ($funcionariosFinais as $func) {
    $nome = str_replace("'", "\\'", $func['nome']);
    $cpf = !empty($func['cpf']) ? "'{$func['cpf']}'" : 'null';
    $pis = $func['pis'] ?: '00000000000';
    $cargo = str_replace("'", "\\'", $func['cargo']);
    $dataAdmissao = $func['data_admissao'] ?: '2020-01-01';
    $departamentoId = $func['departamento_id'];
    $matricula = $func['matricula'];
    
    $peopleOutput .= "            ['id' => {$id}, 'full_name' => '{$nome}', 'cpf' => {$cpf}, 'pis_pasep' => '{$pis}', 'ctps' => null],\n";
    $registrationsOutput .= "            ['id' => {$id}, 'person_id' => {$id}, 'establishment_id' => 1, 'department_id' => {$departamentoId}, 'matricula' => '{$matricula}', 'status' => 'active', 'admission_date' => '{$dataAdmissao}', 'position' => '{$cargo}'],\n";
    
    $id++;
}

$peopleOutput .= "        ];\n    }";
$registrationsOutput .= "        ];\n    }";

file_put_contents($outputDir . '/people_data_final.php', $peopleOutput);
file_put_contents($outputDir . '/registrations_data_final.php', $registrationsOutput);

echo "\n=== ARQUIVOS GERADOS ===\n";
echo "  - people_data_final.php\n";
echo "  - registrations_data_final.php\n";

// ========================================
// PASSO 5: Estatísticas por departamento
// ========================================
echo "\n=== FUNCIONÁRIOS POR DEPARTAMENTO ===\n";
$deptStats = [];
foreach ($funcionariosFinais as $func) {
    $deptId = $func['departamento_id'];
    if (!isset($deptStats[$deptId])) {
        $deptStats[$deptId] = 0;
    }
    $deptStats[$deptId]++;
}
ksort($deptStats);

$deptNames = [
    1 => 'Gabinete do Prefeito',
    2 => 'Secretaria de Finanças',
    3 => 'Secretaria de Administração e RH',
    4 => 'Secretaria de Educação',
    5 => 'Secretaria de Saúde',
    6 => 'Secretaria de Assistência Social',
    7 => 'Secretaria de Esporte e Lazer',
    8 => 'Secretaria de Agricultura',
    9 => 'Secretaria de Desenvolvimento Local',
    10 => 'Secretaria de Obras',
    11 => 'Secretaria do Trabalho',
    12 => 'Secretaria de Cultura e Turismo',
    13 => 'Procuradoria Geral',
    14 => 'SECTI',
    15 => 'Segurança Alimentar',
];

foreach ($deptStats as $deptId => $count) {
    $name = $deptNames[$deptId] ?? "Dept $deptId";
    echo "  $deptId. $name: $count\n";
}

// ========================================
// PASSO 6: Verificar funcionários sem dados essenciais
// ========================================
echo "\n=== VERIFICAÇÃO DE DADOS ===\n";
$semPis = 0;
$semCargo = 0;
$semDataAdmissao = 0;

foreach ($funcionariosFinais as $func) {
    if (empty($func['pis']) || $func['pis'] === '00000000000') $semPis++;
    if (empty($func['cargo']) || $func['cargo'] === 'SERVIDOR PÚBLICO') $semCargo++;
    if (empty($func['data_admissao'])) $semDataAdmissao++;
}

echo "  Sem PIS válido: $semPis\n";
echo "  Sem cargo específico: $semCargo\n";
echo "  Sem data de admissão: $semDataAdmissao\n";
