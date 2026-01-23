<?php
/**
 * Script DEFINITIVO para combinar os dois CSVs
 * 
 * CSV Dinâmico: Tem CPF, PIS e departamento correto (647 funcionários)
 * CSV Listagem: Tem matrícula, PIS, nome, cargo, departamento (961 funcionários)
 * 
 * A chave de ligação é: PIS (presente em ambos)
 */

$csvDinamicoPath = __DIR__ . '/funcionarios_dinamica.csv';
$csvListagemPath = __DIR__ . '/../LISTAGEM DE FUNCIONÁRIOS.csv';

// Mapeamento de departamentos
$departamentosMap = [
    // Do CSV dinâmico
    'GABINETE DO PREFEITO' => 1,
    'SECRETARIA MUNICIPAL DE FINANCAS' => 2,
    'FINANCAS' => 2,
    'FINANÇAS' => 2,
    'SEC MUN DE ADM. E RECURSOS HUMANOS' => 3,
    'ADMINISTRACAO' => 3,
    'ADMINISTRAÇÃO' => 3,
    'RECURSOS HUMANOS' => 3,
    'SECRETARIA MUNICIPAL DE EDUCACAO' => 4,
    'EDUCACAO' => 4,
    'EDUCAÇÃO' => 4,
    'SECRETARIA MUNICIPAL DE SAUDE' => 5,
    'SAUDE' => 5,
    'SAÚDE' => 5,
    'SEC MUNICIPAL DE ASSISTENCIA SOCIAL' => 6,
    'ASSISTENCIA SOCIAL' => 6,
    'ASSISTÊNCIA SOCIAL' => 6,
    'SEC MUN DE ESPORTE E LAZER' => 7,
    'ESPORTE E LAZER' => 7,
    'SEC MUN DE AGRICULTURA E ABASTECIMENTO' => 8,
    'AGRICULTURA' => 8,
    'AGRICULTURA E ABASTECIMENTO' => 8,
    'SECRETARIA MUN DE DESENVOLVIMENTO LOCAL' => 9,
    'DESENVOLVIMENTO' => 9,
    'SEC MUN DE OBRAS E SERVICOS URBANOS' => 10,
    'OBRAS E SERVICOS URBANOS' => 10,
    'OBRAS E SERVIÇOS URBANOS' => 10,
    'OBRAS' => 10,
    'SEC MUN DO TRAB E GERACAO DE EMPREGOS' => 11,
    'TRABALHO' => 11,
    'SEC MUN DE CULTURA E TURISMO' => 12,
    'CULTURA E TURISMO' => 12,
    'CULTURA' => 12,
    'PROCURADORIA GERAL DO MUNICIPIO' => 13,
    'PROCURADORIA' => 13,
    'SECRETARIA MUN DE CIÊNCIA, TECNOLOGIA E INOVAÇÃO' => 14,
    'CIENCIA' => 14,
    'TECNOLOGIA' => 14,
    'SECTI' => 14,
    'SECRETARIA MUN SEGURANÇA ALIMENTAR E NUTRICIONAL' => 15,
    'SEGURANCA ALIMENTAR' => 15,
    'NENHUM' => 4, // Default para Educação
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

function getDepartmentId($deptName, $map) {
    $deptName = mb_strtoupper(trim($deptName), 'UTF-8');
    
    // Match exato
    foreach ($map as $key => $id) {
        if (mb_strtoupper($key, 'UTF-8') === $deptName) {
            return $id;
        }
    }
    
    // Match parcial
    foreach ($map as $key => $id) {
        if (stripos($deptName, $key) !== false || stripos($key, $deptName) !== false) {
            return $id;
        }
    }
    
    return 4; // Default: Educação
}

echo "=== PROCESSAMENTO DEFINITIVO DOS CSVs ===\n\n";

// ========================================
// PASSO 1: Ler CSV Dinâmico (tem CPF e departamento correto)
// Indexar por PIS para fazer match com listagem
// ========================================
$dinamicoPorPis = [];
$dinamicoPorNome = [];
$currentDepartmentId = null;

$lines = file($csvDinamicoPath, FILE_IGNORE_NEW_LINES);
foreach ($lines as $line) {
    // Detectar departamento
    if (preg_match('/DEPARTAMENTO[:\s-]+(.+?)(?:,|$)/i', $line, $matches)) {
        $deptName = trim(str_replace(['"', "'"], '', $matches[1]));
        $currentDepartmentId = getDepartmentId($deptName, $departamentosMap);
        continue;
    }
    
    if (strpos($line, 'Cód. Matrícula') !== false) continue;
    
    $cols = str_getcsv($line);
    if (count($cols) >= 10) {
        $matriculaDinamico = trim($cols[1] ?? '');
        $nome = trim($cols[6] ?? '');
        $cpf = cleanCpf($cols[8] ?? '');
        $pis = cleanPis($cols[9] ?? '');
        $dataAdmissao = parseDate($cols[10] ?? '');
        
        $matriculaDinamico = preg_replace('/\/\d+$/', '', $matriculaDinamico);
        
        if (!empty($pis) && $currentDepartmentId !== null) {
            $dinamicoPorPis[$pis] = [
                'matricula_dinamico' => $matriculaDinamico,
                'nome' => $nome,
                'cpf' => $cpf,
                'pis' => $pis,
                'data_admissao' => $dataAdmissao,
                'departamento_id' => $currentDepartmentId,
            ];
        }
        
        // Também indexar por nome normalizado
        if (!empty($nome)) {
            $nomeNorm = mb_strtoupper(preg_replace('/\s+/', ' ', trim($nome)), 'UTF-8');
            $dinamicoPorNome[$nomeNorm] = $dinamicoPorPis[$pis] ?? null;
        }
    }
}

echo "CSV Dinâmico: " . count($dinamicoPorPis) . " funcionários (por PIS)\n";

// ========================================
// PASSO 2: Ler CSV Listagem (fonte principal - 961 funcionários)
// ========================================
$funcionariosListagem = [];
$content = file_get_contents($csvListagemPath);
$content = mb_convert_encoding($content, 'UTF-8', 'auto');
$lines = explode("\n", $content);

$isFirstLine = true;
foreach ($lines as $line) {
    if ($isFirstLine) {
        $isFirstLine = false;
        continue; // Pular cabeçalho
    }
    
    $line = trim($line);
    if (empty($line)) continue;
    
    $cols = str_getcsv($line, ',');
    if (count($cols) < 8) continue;
    
    // Estrutura: Nº FOLHA, Nº PIS/PASEP, NOME, Nº IDENTIFICADOR, HORÁRIO, FUNÇÃO, DEPARTAMENTO, ADMISSÃO, EMPRESA
    $nFolha = trim($cols[0] ?? '');
    $pis = cleanPis($cols[1] ?? '');
    $nome = trim($cols[2] ?? '');
    $matricula = trim($cols[3] ?? ''); // Nº IDENTIFICADOR é a matrícula real
    $cargo = trim($cols[5] ?? '');
    $departamento = trim($cols[6] ?? '');
    $dataAdmissao = parseDate($cols[7] ?? '');
    
    if (empty($nome)) continue;
    
    // Se matricula estiver vazia, usar nFolha
    if (empty($matricula)) {
        $matricula = $nFolha;
    }
    
    $deptId = getDepartmentId($departamento, $departamentosMap);
    
    $funcionariosListagem[] = [
        'matricula' => $matricula,
        'pis' => $pis,
        'nome' => $nome,
        'cargo' => $cargo ?: 'SERVIDOR PÚBLICO',
        'departamento_id' => $deptId,
        'departamento_nome' => $departamento,
        'data_admissao' => $dataAdmissao,
    ];
}

echo "CSV Listagem: " . count($funcionariosListagem) . " funcionários\n";

// ========================================
// PASSO 3: Combinar dados - Listagem como base, Dinâmico para CPF e dept correto
// ========================================
$funcionariosFinais = [];
$cpfsUsados = [];
$matchesPorPis = 0;
$matchesPorNome = 0;

foreach ($funcionariosListagem as $func) {
    $pis = $func['pis'];
    $nome = $func['nome'];
    $nomeNorm = mb_strtoupper(preg_replace('/\s+/', ' ', trim($nome)), 'UTF-8');
    
    $cpf = null;
    $deptIdFinal = $func['departamento_id'];
    
    // Tentar encontrar no CSV dinâmico por PIS
    if (!empty($pis) && isset($dinamicoPorPis[$pis])) {
        $dinamico = $dinamicoPorPis[$pis];
        $cpf = $dinamico['cpf'];
        $deptIdFinal = $dinamico['departamento_id']; // Departamento do dinâmico é mais confiável
        $matchesPorPis++;
    }
    // Tentar por nome se não encontrou por PIS
    elseif (isset($dinamicoPorNome[$nomeNorm]) && $dinamicoPorNome[$nomeNorm] !== null) {
        $dinamico = $dinamicoPorNome[$nomeNorm];
        $cpf = $dinamico['cpf'];
        $deptIdFinal = $dinamico['departamento_id'];
        $matchesPorNome++;
    }
    
    // Verificar CPF duplicado
    if (!empty($cpf)) {
        if (isset($cpfsUsados[$cpf])) {
            $cpf = null; // Duplicado
        } else {
            $cpfsUsados[$cpf] = true;
        }
    }
    
    $funcionariosFinais[] = [
        'matricula' => $func['matricula'],
        'nome' => $nome,
        'cpf' => $cpf,
        'pis' => $pis ?: '00000000000',
        'cargo' => $func['cargo'],
        'departamento_id' => $deptIdFinal,
        'data_admissao' => $func['data_admissao'] ?: '2020-01-01',
    ];
}

echo "\n=== RESULTADO DO MATCHING ===\n";
echo "Total de funcionários: " . count($funcionariosFinais) . "\n";
echo "Matches por PIS: $matchesPorPis\n";
echo "Matches por Nome: $matchesPorNome\n";
echo "CPFs únicos encontrados: " . count($cpfsUsados) . "\n";

// ========================================
// PASSO 4: Gerar arquivos de saída
// ========================================
$outputDir = __DIR__ . '/seeder_output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$peopleOutput = "<?php\n\n    private function getPeopleData(): array\n    {\n        return [\n";
$registrationsOutput = "<?php\n\n    private function getEmployeeRegistrationsData(): array\n    {\n        return [\n";

$id = 1;
foreach ($funcionariosFinais as $func) {
    $nome = str_replace("'", "\\'", $func['nome']);
    $cpf = !empty($func['cpf']) ? "'{$func['cpf']}'" : 'null';
    $pis = $func['pis'];
    $cargo = str_replace("'", "\\'", $func['cargo']);
    $dataAdmissao = $func['data_admissao'];
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
echo "Total de registros: " . ($id - 1) . "\n";

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
    2 => 'Sec. de Finanças',
    3 => 'Sec. de Administração e RH',
    4 => 'Sec. de Educação',
    5 => 'Sec. de Saúde',
    6 => 'Sec. de Assistência Social',
    7 => 'Sec. de Esporte e Lazer',
    8 => 'Sec. de Agricultura',
    9 => 'Sec. de Desenvolvimento Local',
    10 => 'Sec. de Obras',
    11 => 'Sec. do Trabalho',
    12 => 'Sec. de Cultura e Turismo',
    13 => 'Procuradoria Geral',
    14 => 'SECTI',
    15 => 'Seg. Alimentar',
];

$total = 0;
foreach ($deptStats as $deptId => $count) {
    $name = $deptNames[$deptId] ?? "Dept $deptId";
    printf("  %2d. %-30s %4d\n", $deptId, $name, $count);
    $total += $count;
}
echo "  " . str_repeat('-', 40) . "\n";
echo "      TOTAL:                          $total\n";
