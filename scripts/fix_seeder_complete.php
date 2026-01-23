<?php
/**
 * Script para reconstruir TODOS os dados do seeder a partir dos CSVs originais
 * 
 * CSV1: LISTAGEM DE FUNCIONÁRIOS.csv - Dados completos de funcionários (matrícula, PIS, nome, cargo, departamento, admissão)
 * CSV2: Listagem de funcionários - dinâmica(1).csv - Dados com CPF
 */

// Função para normalizar nomes para comparação
function normalizeName($name) {
    $name = mb_strtoupper(trim($name), 'UTF-8');
    $name = preg_replace('/\s+/', ' ', $name);
    // Remove acentos
    $search = ['Á','À','Â','Ã','Ä','É','È','Ê','Ë','Í','Ì','Î','Ï','Ó','Ò','Ô','Õ','Ö','Ú','Ù','Û','Ü','Ç','Ñ'];
    $replace = ['A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','C','N'];
    $name = str_replace($search, $replace, $name);
    return $name;
}

// Função para limpar PIS (apenas números)
function cleanPis($pis) {
    return preg_replace('/[^0-9]/', '', $pis);
}

// Função para limpar CPF (apenas números)
function cleanCpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    // Adiciona zeros à esquerda se necessário
    if (strlen($cpf) > 0 && strlen($cpf) < 11) {
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
    }
    return $cpf;
}

// Função para limpar matrícula
function cleanMatricula($matricula) {
    // Remove /0 do final e caracteres não numéricos
    $matricula = preg_replace('/\/\d+$/', '', $matricula);
    $matricula = preg_replace('/[^0-9]/', '', $matricula);
    return $matricula;
}

// Função para converter data DD/MM/YYYY para YYYY-MM-DD
function convertDate($dateStr) {
    $dateStr = trim($dateStr);
    if (empty($dateStr)) return null;
    
    // Verifica se já está no formato correto
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
        return $dateStr;
    }
    
    // Converte DD/MM/YYYY ou DD/MM/YY
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $dateStr, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        if (strlen($year) == 2) {
            $year = ($year > 50) ? '19' . $year : '20' . $year;
        }
        return "$year-$month-$day";
    }
    
    return null;
}

// Mapeamento de departamentos
$departmentMap = [
    'ADMINISTRAÇÃO E RECURSOS HUMANOS' => 1,
    'ADMINISTRAÇÃO E RH' => 1,
    'ASSISTÊNCIA SOCIAL' => 4,
    'ASSISTENCIA SOCIAL' => 4,
    'CREAS' => 4,
    'CRAS' => 4,
    'COMUNICAÇÃO' => 5,
    'COMUNICACAO' => 5,
    'CULTURA E TURISMO' => 6,
    'DEFESA CIVIL' => 7,
    'EDUCAÇÃO' => 8,
    'EDUCACAO' => 8,
    'ESPORTE E LAZER' => 9,
    'FINANÇAS' => 10,
    'FINANCAS' => 10,
    'GABINETE DO PREFEITO' => 11,
    'GABINETE' => 11,
    'NENHUM' => 12,
    'OBRAS E SERVIÇOS URBANOS' => 13,
    'OBRAS' => 13,
    'PROCURADORIA MUNICIPAL' => 14,
    'PROCURADORIA' => 14,
    'PÓLO UAB' => 15,
    'POLO UAB' => 15,
    'UAB' => 15,
    'SAÚDE' => 16,
    'SAUDE' => 16,
    'SEGURANÇA ALIMENTAR E NUTRICIONAL' => 17,
    'SEGURANCA ALIMENTAR' => 17,
    'BANCO DE ALIMENTOS' => 17,
    'TRABALHO E GERAÇÃO DE EMPREGOS' => 18,
    'TRABALHO' => 18,
    'AGÊNCIA DO TRABALHADOR' => 18,
    'AGRICULTURA' => 3,
    'MEIO AMBIENTE' => 2,
];

function getDepartmentId($deptName, $departmentMap) {
    $deptName = mb_strtoupper(trim($deptName), 'UTF-8');
    
    // Remove "SECRETARIA: " do início se existir
    $deptName = preg_replace('/^SECRETARIA:\s*/i', '', $deptName);
    $deptName = preg_replace('/^\d+\s*-\s*/', '', $deptName); // Remove números no início
    
    if (isset($departmentMap[$deptName])) {
        return $departmentMap[$deptName];
    }
    
    // Tenta match parcial
    foreach ($departmentMap as $key => $id) {
        if (strpos($deptName, $key) !== false || strpos($key, $deptName) !== false) {
            return $id;
        }
    }
    
    // Padrão: NENHUM
    return 12;
}

echo "=== INICIANDO PROCESSAMENTO DOS CSVs ===\n\n";

// =============================================
// PASSO 1: Ler CSV1 (LISTAGEM DE FUNCIONÁRIOS.csv)
// =============================================
echo "1. Lendo CSV1: LISTAGEM DE FUNCIONÁRIOS.csv\n";

$csv1Path = __DIR__ . '/../LISTAGEM DE FUNCIONÁRIOS.csv';
$csv1Content = file_get_contents($csv1Path);

// Normaliza quebras de linha e trata problemas de encoding
$csv1Content = str_replace(["\r\n", "\r"], "\n", $csv1Content);
$csv1Content = mb_convert_encoding($csv1Content, 'UTF-8', 'auto');

$csv1Lines = explode("\n", $csv1Content);

// A primeira linha é cabeçalho: Nº FOLHA,Nº PIS/PASEP,NOME,Nº IDENTIFICADOR,HORÁRIO,FUNÇÃO,DEPARTAMENTO,ADMISSÃO,EMPRESA
$employees1 = [];

for ($i = 1; $i < count($csv1Lines); $i++) {
    $line = trim($csv1Lines[$i]);
    if (empty($line)) continue;
    
    // Parse CSV - usando str_getcsv para lidar com vírgulas dentro de campos
    $fields = str_getcsv($line);
    
    if (count($fields) < 8) continue;
    
    $folha = trim($fields[0]);
    $pis = cleanPis($fields[1]);
    $name = mb_strtoupper(trim($fields[2]), 'UTF-8');
    $matricula = cleanMatricula($fields[3]);
    $horario = trim($fields[4]);
    $funcao = trim($fields[5]);
    $departamento = trim($fields[6]);
    $admissao = convertDate($fields[7]);
    
    if (empty($name) || strlen($name) < 3) continue;
    if (empty($matricula)) {
        $matricula = $folha; // Usa número da folha como matrícula se não tiver
    }
    
    $employees1[] = [
        'matricula' => $matricula,
        'pis' => $pis,
        'name' => $name,
        'position' => $funcao,
        'department' => $departamento,
        'admission_date' => $admissao,
    ];
}

echo "   -> Encontrados: " . count($employees1) . " funcionários no CSV1\n\n";

// =============================================
// PASSO 2: Ler CSV2 (Listagem de funcionários - dinâmica.csv) para CPF
// =============================================
echo "2. Lendo CSV2: Listagem de funcionários - dinâmica(1).csv\n";

$csv2Path = __DIR__ . '/../Listagem de funcionários - dinâmica(1).csv';
$csv2Content = file_get_contents($csv2Path);
$csv2Content = str_replace(["\r\n", "\r"], "\n", $csv2Content);
$csv2Content = mb_convert_encoding($csv2Content, 'UTF-8', 'auto');

$csv2Lines = explode("\n", $csv2Content);

// Mapeamento CPF por matrícula e por nome
$cpfByMatricula = [];
$cpfByName = [];
$cpfByPis = [];

foreach ($csv2Lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    $fields = str_getcsv($line);
    
    // Procura por padrão de matrícula: número/0
    // Formato: ,1484/0,,,,,CLAUDINEIA DOS SANTOS,,832.099.809-34  ,12435614456,01/07/1998,,,,,,,,,
    
    // Encontra a matrícula (padrão: número/0 ou número/1)
    $matricula = '';
    $name = '';
    $cpf = '';
    $pis = '';
    
    foreach ($fields as $idx => $field) {
        $field = trim($field);
        
        // Matrícula: padrão XXXX/0
        if (preg_match('/^(\d+)\/\d+$/', $field, $m)) {
            $matricula = $m[1];
        }
        // CPF: padrão XXX.XXX.XXX-XX
        elseif (preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}/', $field)) {
            $cpf = cleanCpf($field);
        }
        // PIS: 11 dígitos
        elseif (preg_match('/^\d{11}$/', $field)) {
            $pis = $field;
        }
        // Nome: texto com mais de 5 caracteres que não é data nem número
        elseif (strlen($field) > 5 && !preg_match('/^\d/', $field) && !preg_match('/^Secretaria:/i', $field) && !preg_match('/\d{2}\/\d{2}\/\d{4}/', $field)) {
            if (empty($name)) {
                $name = mb_strtoupper($field, 'UTF-8');
            }
        }
    }
    
    if (!empty($cpf) && strlen($cpf) == 11) {
        if (!empty($matricula)) {
            $cpfByMatricula[$matricula] = $cpf;
        }
        if (!empty($name)) {
            $normalizedName = normalizeName($name);
            $cpfByName[$normalizedName] = $cpf;
        }
        if (!empty($pis)) {
            $cpfByPis[$pis] = $cpf;
        }
    }
}

echo "   -> CPFs por matrícula: " . count($cpfByMatricula) . "\n";
echo "   -> CPFs por nome: " . count($cpfByName) . "\n";
echo "   -> CPFs por PIS: " . count($cpfByPis) . "\n\n";

// =============================================
// PASSO 3: Combinar dados - Encontrar CPF para cada funcionário
// =============================================
echo "3. Combinando dados (vinculando CPF aos funcionários)\n";

$peopleData = [];
$registrationsData = [];

$matchedCpf = 0;
$notMatchedCpf = 0;

$personId = 1;

foreach ($employees1 as $emp) {
    $cpf = null;
    $matchMethod = '';
    
    // Tentativa 1: Por matrícula
    if (!empty($emp['matricula']) && isset($cpfByMatricula[$emp['matricula']])) {
        $cpf = $cpfByMatricula[$emp['matricula']];
        $matchMethod = 'matricula';
    }
    
    // Tentativa 2: Por PIS
    if (empty($cpf) && !empty($emp['pis']) && isset($cpfByPis[$emp['pis']])) {
        $cpf = $cpfByPis[$emp['pis']];
        $matchMethod = 'pis';
    }
    
    // Tentativa 3: Por nome normalizado
    if (empty($cpf)) {
        $normalizedName = normalizeName($emp['name']);
        if (isset($cpfByName[$normalizedName])) {
            $cpf = $cpfByName[$normalizedName];
            $matchMethod = 'nome';
        }
    }
    
    if (!empty($cpf)) {
        $matchedCpf++;
    } else {
        $notMatchedCpf++;
    }
    
    // Adiciona pessoa
    $peopleData[] = [
        'id' => $personId,
        'full_name' => $emp['name'],
        'cpf' => $cpf,
        'pis_pasep' => $emp['pis'],
        'ctps' => null,
    ];
    
    // Adiciona vínculo
    $registrationsData[] = [
        'id' => $personId,
        'person_id' => $personId,
        'establishment_id' => 1,
        'department_id' => getDepartmentId($emp['department'], $departmentMap),
        'matricula' => $emp['matricula'],
        'status' => 'active',
        'admission_date' => $emp['admission_date'],
        'position' => $emp['position'],
    ];
    
    $personId++;
}

echo "   -> Total de pessoas: " . count($peopleData) . "\n";
echo "   -> Com CPF: $matchedCpf\n";
echo "   -> Sem CPF: $notMatchedCpf\n";
echo "   -> Total de vínculos: " . count($registrationsData) . "\n\n";

// =============================================
// PASSO 4: Gerar código PHP para o seeder
// =============================================
echo "4. Gerando código para o seeder...\n";

$outputDir = __DIR__ . '/seeder_output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Gerar getPeopleData()
$peopleCode = "    private function getPeopleData(): array\n    {\n        return [\n";
foreach ($peopleData as $person) {
    $cpfStr = $person['cpf'] ? "'{$person['cpf']}'" : 'null';
    $pisStr = $person['pis_pasep'] ? "'{$person['pis_pasep']}'" : 'null';
    $nameEscaped = str_replace("'", "\\'", $person['full_name']);
    $peopleCode .= "            ['id' => {$person['id']}, 'full_name' => '{$nameEscaped}', 'cpf' => $cpfStr, 'pis_pasep' => $pisStr, 'ctps' => null],\n";
}
$peopleCode .= "        ];\n    }";

file_put_contents($outputDir . '/people_data.php', "<?php\n\n" . $peopleCode);
echo "   -> Gerado: seeder_output/people_data.php\n";

// Gerar getEmployeeRegistrationsData()
$regsCode = "    private function getEmployeeRegistrationsData(): array\n    {\n        return [\n";
foreach ($registrationsData as $reg) {
    $dateStr = $reg['admission_date'] ? "'{$reg['admission_date']}'" : 'null';
    $positionEscaped = str_replace("'", "\\'", $reg['position']);
    $regsCode .= "            ['id' => {$reg['id']}, 'person_id' => {$reg['person_id']}, 'establishment_id' => 1, 'department_id' => {$reg['department_id']}, 'matricula' => '{$reg['matricula']}', 'status' => 'active', 'admission_date' => $dateStr, 'position' => '{$positionEscaped}'],\n";
}
$regsCode .= "        ];\n    }";

file_put_contents($outputDir . '/registrations_data.php', "<?php\n\n" . $regsCode);
echo "   -> Gerado: seeder_output/registrations_data.php\n";

echo "\n=== PROCESSAMENTO CONCLUÍDO ===\n";
echo "\nResumo:\n";
echo "- Pessoas: " . count($peopleData) . "\n";
echo "- Vínculos: " . count($registrationsData) . "\n";
echo "- CPFs encontrados: $matchedCpf (" . round(($matchedCpf / count($peopleData)) * 100, 1) . "%)\n";
echo "\nPróximo passo: Copie o conteúdo dos arquivos gerados para o ProductionDataSeeder.php\n";
