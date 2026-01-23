<?php

/**
 * Script final para corrigir os últimos funcionários
 * e remover o registro de teste 'ujnijnjnjin'
 */

$seederPath = __DIR__ . '/../database/seeders/ProductionDataSeeder.php';

// Dados extraídos do segundo CSV para os funcionários restantes
$remainingEmployees = [
    'IANI FAVARO CASAGRANDE' => ['admission_date' => '2025-08-19', 'position' => 'ESTAGIÁRIO'],
    'LUMA DUQUE BORTOLUZZI' => ['admission_date' => '2025-10-24', 'position' => 'MÉDICO'],
    'ORLANDO DOS SANTOS JUNIOR' => ['admission_date' => '2021-10-01', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
    'ORLANDO MENEGAZZO FILHO' => ['admission_date' => '2021-01-04', 'position' => 'CONTADOR'],
    'OSCAR FRANCISCO DAS NEVES' => ['admission_date' => '2020-01-15', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'], // Data aproximada
    'PAMELA FONSECA RIBAS GIUNTA' => ['admission_date' => '2025-08-04', 'position' => 'MÉDICO'],
    'SANDRO COLHERI' => ['admission_date' => '2023-03-01', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
    'VICTOR HUGO MARTELI GRACIONALI' => ['admission_date' => '2025-10-01', 'position' => 'ESTAGIÁRIO'],
    'WASHINGTON RAFAEL PROENCA DA FONSECA' => ['admission_date' => '2025-10-22', 'position' => 'MÉDICO'],
    'DIONATAN PAIVA DIAS' => ['admission_date' => '2025-10-01', 'position' => 'ESTAGIÁRIO'],
];

echo "=== CORREÇÃO FINAL ===\n\n";

$seederContent = file_get_contents($seederPath);

// Extrair getPeopleData
preg_match('/private function getPeopleData\(\): array\s*\{\s*return\s*\[(.*?)\];\s*\}/s', $seederContent, $peopleMatch);
$peopleArrayStr = $peopleMatch[1] ?? '';

// Criar mapeamento nome -> person_id
$nameToPersonId = [];
preg_match_all("/\['id' => (\d+), 'full_name' => '([^']+)'/", $peopleArrayStr, $peopleMatches, PREG_SET_ORDER);
foreach ($peopleMatches as $match) {
    $nameToPersonId[strtoupper(trim($match[2]))] = (int)$match[1];
}

// Para cada funcionário faltante, atualizar o seeder
$updates = 0;
foreach ($remainingEmployees as $name => $data) {
    $nameUpper = strtoupper(trim($name));
    
    // Encontrar person_id
    $personId = $nameToPersonId[$nameUpper] ?? null;
    
    if (!$personId) {
        // Tentar variações do nome
        foreach ($nameToPersonId as $seederName => $id) {
            // Comparar sem acentos/especiais
            $seederNameClean = preg_replace('/[^A-Z0-9\s]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $seederName));
            $nameClean = preg_replace('/[^A-Z0-9\s]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nameUpper));
            
            if ($seederNameClean === $nameClean) {
                $personId = $id;
                echo "  Match por normalização: $name -> $seederName\n";
                break;
            }
        }
    }
    
    if (!$personId) {
        echo "AVISO: Pessoa '$name' não encontrada no seeder\n";
        continue;
    }
    
    // Procurar e substituir a linha correspondente
    $pattern = "/(\['id' => \d+, 'person_id' => $personId, [^]]*'admission_date' => )'2020-01-01'(, 'position' => )null(\])/";
    $replacement = "\${1}'{$data['admission_date']}'\${2}'{$data['position']}'\${3}";
    
    $newContent = preg_replace($pattern, $replacement, $seederContent);
    
    if ($newContent !== $seederContent) {
        $seederContent = $newContent;
        $updates++;
        echo "✓ Atualizado: $name (person_id: $personId)\n";
    } else {
        echo "AVISO: Não foi possível atualizar '$name' (person_id: $personId) - pode já estar correto\n";
    }
}

// Remover o registro de teste 'ujnijnjnjin'
echo "\nRemovendo registro de teste 'ujnijnjnjin'...\n";

// Encontrar o person_id de ujnijnjnjin
$testPersonId = $nameToPersonId['UJNIJNJNJIN'] ?? null;

if ($testPersonId) {
    // Remover da people array
    $pattern1 = '/\s*\[\'id\' => ' . $testPersonId . ', \'full_name\' => \'ujnijnjnjin\'[^\]]*\],?/i';
    $seederContent = preg_replace($pattern1, '', $seederContent);
    
    // Remover da employee_registrations array
    $pattern2 = '/\s*\[\'id\' => \d+, \'person_id\' => ' . $testPersonId . '[^\]]*\],?/';
    $seederContent = preg_replace($pattern2, '', $seederContent);
    
    echo "✓ Registro de teste removido (person_id: $testPersonId)\n";
}

// Salvar
file_put_contents($seederPath, $seederContent);

echo "\n=== RESULTADO ===\n";
echo "Total de atualizações: $updates\n";

// Verificar quantos ainda faltam
preg_match_all("/admission_date' => '2020-01-01'/", $seederContent, $remaining);
echo "Registros ainda com placeholder: " . count($remaining[0]) . "\n";
