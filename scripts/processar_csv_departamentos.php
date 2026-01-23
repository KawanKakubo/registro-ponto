<?php
/**
 * Script para processar o CSV de funcionários por departamento
 * e gerar os dados completos para o ProductionDataSeeder
 */

$csvPath = __DIR__ . '/funcionarios_dinamica.csv';
$csvListagem = __DIR__ . '/../LISTAGEM DE FUNCIONÁRIOS.csv';

// Mapeamento de departamentos do CSV para IDs do banco
$departamentosMap = [
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

function normalizeName($name) {
    $name = mb_strtoupper(trim($name), 'UTF-8');
    $name = preg_replace('/\s+/', ' ', $name);
    // Remove acentos para comparação
    $acentos = ['Á','À','Ã','Â','É','È','Ê','Í','Ì','Ó','Ò','Õ','Ô','Ú','Ù','Ç'];
    $semAcentos = ['A','A','A','A','E','E','E','I','I','O','O','O','O','U','U','C'];
    return str_replace($acentos, $semAcentos, $name);
}

function cleanCpf($cpf) {
    return preg_replace('/[^0-9]/', '', $cpf);
}

function cleanPis($pis) {
    $pis = preg_replace('/[^0-9]/', '', $pis);
    // Garantir que não ultrapasse 11 dígitos
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

echo "=== PROCESSANDO CSV DE FUNCIONÁRIOS POR DEPARTAMENTO ===\n\n";

// Ler CSV dinâmico (com departamentos e CPF)
$funcionariosPorDepartamento = [];
$currentDepartment = null;
$currentDepartmentId = null;

$lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // Detectar linha de departamento
    if (preg_match('/DEPARTAMENTO[:\s-]+(.+?)(?:,|$)/i', $line, $matches)) {
        $deptName = trim($matches[1]);
        $deptName = str_replace(['"', "'"], '', $deptName);
        $deptName = trim($deptName);
        
        // Encontrar o ID do departamento
        $currentDepartmentId = null;
        foreach ($departamentosMap as $key => $id) {
            if (stripos($deptName, $key) !== false || stripos($key, $deptName) !== false) {
                $currentDepartmentId = $id;
                break;
            }
        }
        
        if ($currentDepartmentId === null) {
            // Tentar match parcial
            foreach ($departamentosMap as $key => $id) {
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
        
        $currentDepartment = $deptName;
        continue;
    }
    
    // Pular linhas de cabeçalho
    if (strpos($line, 'Cód. Matrícula') !== false || strpos($line, 'Nome') !== false) {
        continue;
    }
    
    // Processar linha de funcionário
    $cols = str_getcsv($line);
    
    // A estrutura é: ,Matrícula,,,,,Nome,,CPF,PIS,Data Admissão
    if (count($cols) >= 10) {
        $matricula = trim($cols[1] ?? '');
        $nome = trim($cols[6] ?? '');
        $cpf = cleanCpf($cols[8] ?? '');
        $pis = cleanPis($cols[9] ?? '');
        $dataAdmissao = parseDate($cols[10] ?? '');
        
        // Remover /0 da matrícula
        $matricula = preg_replace('/\/\d+$/', '', $matricula);
        
        if (!empty($nome) && !empty($matricula) && $currentDepartmentId !== null) {
            $funcionariosPorDepartamento[] = [
                'matricula' => $matricula,
                'nome' => $nome,
                'cpf' => $cpf,
                'pis' => $pis,
                'data_admissao' => $dataAdmissao,
                'departamento_id' => $currentDepartmentId,
                'departamento_nome' => $currentDepartment,
            ];
        }
    }
}

echo "Funcionários encontrados no CSV dinâmico: " . count($funcionariosPorDepartamento) . "\n";

// Ler CSV de listagem (para pegar cargo/função)
$listagemPorMatricula = [];
if (file_exists($csvListagem)) {
    $listagemLines = file($csvListagem, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
            $listagemPorMatricula[$matricula] = [
                'pis' => $pis,
                'nome' => $nome,
                'cargo' => $cargo,
                'departamento' => $departamento,
                'data_admissao' => $dataAdmissao,
            ];
        }
    }
    echo "Funcionários encontrados no CSV de listagem: " . count($listagemPorMatricula) . "\n";
}

// Consolidar dados - usar CSV dinâmico como base (tem CPF)
$funcionariosFinais = [];
$cpfsUsados = [];

foreach ($funcionariosPorDepartamento as $func) {
    $matricula = $func['matricula'];
    $nome = $func['nome'];
    $cpf = $func['cpf'];
    $pis = $func['pis'];
    $dataAdmissao = $func['data_admissao'];
    $departamentoId = $func['departamento_id'];
    $cargo = 'SERVIDOR PÚBLICO';
    
    // Tentar obter cargo da listagem
    if (isset($listagemPorMatricula[$matricula])) {
        $cargo = $listagemPorMatricula[$matricula]['cargo'] ?: $cargo;
        if (empty($pis)) {
            $pis = $listagemPorMatricula[$matricula]['pis'];
        }
        if (empty($dataAdmissao)) {
            $dataAdmissao = $listagemPorMatricula[$matricula]['data_admissao'];
        }
    }
    
    // Verificar CPF duplicado
    if (!empty($cpf) && isset($cpfsUsados[$cpf])) {
        // CPF duplicado - manter apenas o primeiro
        $cpf = null;
    }
    
    if (!empty($cpf)) {
        $cpfsUsados[$cpf] = true;
    }
    
    $funcionariosFinais[] = [
        'matricula' => $matricula,
        'nome' => $nome,
        'cpf' => $cpf,
        'pis' => $pis,
        'data_admissao' => $dataAdmissao ?: '2020-01-01',
        'departamento_id' => $departamentoId,
        'cargo' => $cargo,
    ];
}

echo "\nFuncionários consolidados: " . count($funcionariosFinais) . "\n";
echo "Com CPF único: " . count($cpfsUsados) . "\n";

// Gerar arquivos de saída
$outputDir = __DIR__ . '/seeder_output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Gerar people_data.php
$peopleOutput = "<?php\n\n    private function getPeopleData(): array\n    {\n        return [\n";
$registrationsOutput = "<?php\n\n    private function getEmployeeRegistrationsData(): array\n    {\n        return [\n";

$id = 1;
foreach ($funcionariosFinais as $func) {
    $nome = addslashes($func['nome']);
    $cpf = $func['cpf'] ? "'{$func['cpf']}'" : 'null';
    $pis = $func['pis'] ?: '00000000000';
    $cargo = addslashes($func['cargo']);
    $dataAdmissao = $func['data_admissao'];
    $departamentoId = $func['departamento_id'];
    $matricula = $func['matricula'];
    
    $peopleOutput .= "            ['id' => {$id}, 'full_name' => '{$nome}', 'cpf' => {$cpf}, 'pis_pasep' => '{$pis}', 'ctps' => null],\n";
    $registrationsOutput .= "            ['id' => {$id}, 'person_id' => {$id}, 'establishment_id' => 1, 'department_id' => {$departamentoId}, 'matricula' => '{$matricula}', 'status' => 'active', 'admission_date' => '{$dataAdmissao}', 'position' => '{$cargo}'],\n";
    
    $id++;
}

$peopleOutput .= "        ];\n    }";
$registrationsOutput .= "        ];\n    }";

file_put_contents($outputDir . '/people_data_v2.php', $peopleOutput);
file_put_contents($outputDir . '/registrations_data_v2.php', $registrationsOutput);

echo "\nArquivos gerados em: $outputDir\n";
echo "  - people_data_v2.php ({$id} registros)\n";
echo "  - registrations_data_v2.php ({$id} registros)\n";

// Listar departamentos encontrados
echo "\n=== DEPARTAMENTOS DETECTADOS ===\n";
$deptCount = [];
foreach ($funcionariosFinais as $func) {
    $deptId = $func['departamento_id'];
    if (!isset($deptCount[$deptId])) {
        $deptCount[$deptId] = 0;
    }
    $deptCount[$deptId]++;
}
ksort($deptCount);
foreach ($deptCount as $deptId => $count) {
    echo "  Departamento $deptId: $count funcionários\n";
}
