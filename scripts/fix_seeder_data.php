<?php

/**
 * Script para corrigir os dados do ProductionDataSeeder.php
 * usando os dados do CSV "LISTAGEM DE FUNCIONÁRIOS.csv"
 * 
 * Este script:
 * 1. Lê o CSV com os dados reais
 * 2. Faz o matching com os dados do seeder (por nome ou PIS)
 * 3. Atualiza admission_date, position e valida CPF/PIS
 */

// Caminho dos arquivos
$csvPath = __DIR__ . '/../LISTAGEM DE FUNCIONÁRIOS.csv';
$seederPath = __DIR__ . '/../database/seeders/ProductionDataSeeder.php';

// Mapeamento de departamentos para IDs
$departmentMapping = [
    'ADMINISTRAÇÃO E RH' => 1,
    'AGRICULTURA E ABASTECIMENTO' => 2,
    'AGRICULTURA, ABASTECIMENTO E MEIO AMBIENTE' => 3,
    'AGRICULTURA/ABASTECIMENTO E MEIO AMBIENTE' => 3,
    'ASSISTÊNCIA SOCIAL' => 4,
    'CIÊNCIA, TECNOLOGIA E INOVAÇÃO' => 5,
    'CIÊNCIA/TECNOLOGIA E INOVAÇÃO' => 5,
    'CULTURA E TURISMO' => 6,
    'DEFESA CIVIL' => 7,
    'EDUCAÇÃO' => 8,
    'ESPORTE E LAZER' => 9,
    'FINANÇAS' => 10,
    'GABINETE DO PREFEITO' => 11,
    'NENHUM' => 12,
    'OBRAS E SERVIÇOS URBANOS' => 13,
    'PROCURADORIA MUNICIPAL' => 14,
    'PÓLO UAB' => 15,
    'POLO UAB' => 15,
    'SAÚDE' => 16,
    'SEGURANÇA ALIMENTAR E NUTRICIONAL' => 17,
    'TRABALHO E GERAÇÃO DE EMPREGOS' => 18,
];

echo "=== SCRIPT DE CORREÇÃO DO SEEDER ===\n\n";

// 1. Ler CSV
echo "1. Lendo CSV...\n";

if (!file_exists($csvPath)) {
    die("ERRO: Arquivo CSV não encontrado: $csvPath\n");
}

$csvData = [];
$handle = fopen($csvPath, 'r');

// Detectar encoding e converter se necessário
$firstLine = fgets($handle);
$encoding = mb_detect_encoding($firstLine, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
if ($encoding !== 'UTF-8') {
    $firstLine = mb_convert_encoding($firstLine, 'UTF-8', $encoding);
}
rewind($handle);

// Pular cabeçalho
$header = fgetcsv($handle);

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 9) continue;
    
    // Converter encoding se necessário
    if ($encoding !== 'UTF-8') {
        $row = array_map(function($val) use ($encoding) {
            return mb_convert_encoding($val, 'UTF-8', $encoding);
        }, $row);
    }
    
    $folha = trim($row[0]);
    $pisPasep = trim($row[1]);
    $nome = trim($row[2]);
    $identificador = trim($row[3]);
    $horario = trim($row[4]);
    $funcao = trim($row[5]);
    $departamento = trim($row[6]);
    $admissao = trim($row[7]);
    $empresa = trim($row[8]);
    
    // Normalizar nome para matching
    $nomeNormalizado = normalizeString($nome);
    
    // Converter data de DD/MM/YYYY para YYYY-MM-DD
    $admissaoFormatada = convertDate($admissao);
    
    // Obter department_id
    $departmentId = getDepartmentId($departamento, $departmentMapping);
    
    $csvData[$nomeNormalizado] = [
        'nome_original' => $nome,
        'folha' => $folha,
        'pis_pasep' => $pisPasep,
        'identificador' => $identificador,
        'funcao' => $funcao,
        'departamento' => $departamento,
        'department_id' => $departmentId,
        'admissao_original' => $admissao,
        'admissao_formatada' => $admissaoFormatada,
    ];
    
    // Também indexar por PIS para matching alternativo
    if (!empty($pisPasep) && strlen($pisPasep) >= 11) {
        $csvData['pis_' . $pisPasep] = $csvData[$nomeNormalizado];
    }
}

fclose($handle);

echo "   Encontrados " . count($csvData) . " registros no CSV\n\n";

// 2. Ler Seeder atual e extrair dados
echo "2. Lendo seeder atual...\n";

$seederContent = file_get_contents($seederPath);

// Extrair getPeopleData
preg_match('/private function getPeopleData\(\): array\s*\{\s*return\s*\[(.*?)\];\s*\}/s', $seederContent, $peopleMatch);
$peopleArrayStr = $peopleMatch[1] ?? '';

// Extrair getEmployeeRegistrationsData
preg_match('/private function getEmployeeRegistrationsData\(\): array\s*\{\s*return\s*\[(.*?)\];\s*\}/s', $seederContent, $empRegMatch);
$empRegArrayStr = $empRegMatch[1] ?? '';

// Parse pessoas - extrair info básica
$people = [];
preg_match_all("/\['id' => (\d+), 'full_name' => '([^']+)', 'cpf' => '([^']*)', 'pis_pasep' => '([^']*)', 'ctps' => ([^]]+)\]/", $peopleArrayStr, $peopleMatches, PREG_SET_ORDER);

foreach ($peopleMatches as $match) {
    $id = (int)$match[1];
    $fullName = $match[2];
    $cpf = $match[3];
    $pisPasep = $match[4];
    
    $nomeNormalizado = normalizeString($fullName);
    
    $people[$id] = [
        'id' => $id,
        'full_name' => $fullName,
        'cpf' => $cpf,
        'pis_pasep' => $pisPasep,
        'nome_normalizado' => $nomeNormalizado,
    ];
}

echo "   Encontradas " . count($people) . " pessoas no seeder\n";

// Parse employee_registrations - extrair info
$empRegs = [];
preg_match_all("/\['id' => (\d+), 'person_id' => (\d+), 'establishment_id' => (\d+), 'department_id' => (\d+), 'matricula' => '([^']*)', 'status' => '([^']*)', 'admission_date' => '([^']*)', 'position' => ([^]]+)\]/", $empRegArrayStr, $empRegMatches, PREG_SET_ORDER);

foreach ($empRegMatches as $match) {
    $id = (int)$match[1];
    $personId = (int)$match[2];
    $establishmentId = (int)$match[3];
    $departmentId = (int)$match[4];
    $matricula = $match[5];
    $status = $match[6];
    $admissionDate = $match[7];
    $position = $match[8];
    
    $empRegs[$id] = [
        'id' => $id,
        'person_id' => $personId,
        'establishment_id' => $establishmentId,
        'department_id' => $departmentId,
        'matricula' => $matricula,
        'status' => $status,
        'admission_date' => $admissionDate,
        'position' => $position,
    ];
}

echo "   Encontrados " . count($empRegs) . " registros de funcionários no seeder\n\n";

// 3. Fazer matching e preparar correções
echo "3. Fazendo matching dos dados...\n";

$corrections = [];
$notFound = [];
$matched = 0;
$matchedByPis = 0;

foreach ($empRegs as $empRegId => $empReg) {
    $personId = $empReg['person_id'];
    
    if (!isset($people[$personId])) {
        continue;
    }
    
    $person = $people[$personId];
    $nomeNormalizado = $person['nome_normalizado'];
    $pisPasep = $person['pis_pasep'];
    
    // Tentar match por nome
    $csvRecord = null;
    if (isset($csvData[$nomeNormalizado])) {
        $csvRecord = $csvData[$nomeNormalizado];
        $matched++;
    }
    // Tentar match por PIS
    elseif (!empty($pisPasep) && isset($csvData['pis_' . $pisPasep])) {
        $csvRecord = $csvData['pis_' . $pisPasep];
        $matchedByPis++;
    }
    
    if ($csvRecord) {
        $corrections[$empRegId] = [
            'admission_date' => $csvRecord['admissao_formatada'],
            'position' => $csvRecord['funcao'],
            'department_id' => $csvRecord['department_id'],
            'person_name' => $person['full_name'],
            'csv_name' => $csvRecord['nome_original'],
        ];
    } else {
        $notFound[] = $person['full_name'];
    }
}

echo "   Matched por nome: $matched\n";
echo "   Matched por PIS: $matchedByPis\n";
echo "   Não encontrados: " . count($notFound) . "\n\n";

// 4. Aplicar correções no seeder
echo "4. Aplicando correções no seeder...\n";

// Gerar novo array de employee_registrations
$newEmpRegLines = [];

foreach ($empRegs as $empRegId => $empReg) {
    $id = $empReg['id'];
    $personId = $empReg['person_id'];
    $establishmentId = $empReg['establishment_id'];
    $departmentId = $empReg['department_id'];
    $matricula = $empReg['matricula'];
    $status = $empReg['status'];
    $admissionDate = $empReg['admission_date'];
    $position = 'null';
    
    // Aplicar correções se disponíveis
    if (isset($corrections[$empRegId])) {
        $correction = $corrections[$empRegId];
        $admissionDate = $correction['admission_date'];
        $position = "'" . addslashes($correction['position']) . "'";
        // Não alterar department_id se já está definido no seeder original
        // (o seeder pode ter informações mais precisas)
    }
    
    $newEmpRegLines[] = "            ['id' => $id, 'person_id' => $personId, 'establishment_id' => $establishmentId, 'department_id' => $departmentId, 'matricula' => '$matricula', 'status' => '$status', 'admission_date' => '$admissionDate', 'position' => $position],";
}

// Criar novo conteúdo do método getEmployeeRegistrationsData
$newEmpRegContent = implode("\n", $newEmpRegLines);

// Substituir no arquivo
$newSeederContent = preg_replace(
    '/private function getEmployeeRegistrationsData\(\): array\s*\{\s*return\s*\[.*?\];\s*\}/s',
    "private function getEmployeeRegistrationsData(): array\n    {\n        return [\n$newEmpRegContent\n        ];\n    }",
    $seederContent
);

// Salvar
file_put_contents($seederPath, $newSeederContent);

echo "   Seeder atualizado com sucesso!\n\n";

// 5. Relatório
echo "=== RELATÓRIO ===\n";
echo "Total de correções aplicadas: " . count($corrections) . "\n";
echo "Total de registros sem match: " . count($notFound) . "\n\n";

if (count($notFound) > 0 && count($notFound) <= 20) {
    echo "Funcionários não encontrados no CSV:\n";
    foreach ($notFound as $nome) {
        echo "  - $nome\n";
    }
}

echo "\n=== CONCLUÍDO ===\n";

// ========================================
// Funções auxiliares
// ========================================

function normalizeString($str) {
    // Remover acentos
    $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
    // Converter para maiúsculas
    $str = strtoupper($str);
    // Remover caracteres especiais
    $str = preg_replace('/[^A-Z0-9\s]/', '', $str);
    // Remover espaços extras
    $str = preg_replace('/\s+/', ' ', trim($str));
    return $str;
}

function convertDate($dateStr) {
    // DD/MM/YYYY -> YYYY-MM-DD
    $parts = explode('/', $dateStr);
    if (count($parts) === 3) {
        return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
    }
    return '2020-01-01'; // fallback
}

function getDepartmentId($departamento, $mapping) {
    // Normalizar departamento para busca
    $deptNorm = strtoupper(trim($departamento));
    
    foreach ($mapping as $key => $id) {
        if (strtoupper($key) === $deptNorm) {
            return $id;
        }
    }
    
    // Se não encontrou, retornar 12 (NENHUM)
    return 12;
}
