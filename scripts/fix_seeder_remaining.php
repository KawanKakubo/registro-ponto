<?php

/**
 * Script complementar para corrigir os funcionários restantes
 * usando dados do segundo CSV "Listagem de funcionários - dinâmica(1).csv"
 */

$seederPath = __DIR__ . '/../database/seeders/ProductionDataSeeder.php';

// Dados extraídos manualmente do segundo CSV para os funcionários faltantes
// Formato: 'NOME' => ['admission_date' => 'YYYY-MM-DD', 'cpf' => 'CPF limpo']
$missingEmployees = [
    'ADALTON ROSA ARAUJO' => ['admission_date' => '2024-09-23', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
    'ADILSON LOPES' => ['admission_date' => '2025-01-20', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
    'ALI ANUAR CHEHADE' => ['admission_date' => '2025-10-07', 'position' => 'MÉDICO'],
    'ANDERSON SIMAO' => ['admission_date' => '2022-02-11', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
    'ANITA BORGES CHEHADE' => ['admission_date' => '2025-10-01', 'position' => 'AGENTE AUXILIAR ADMINISTRATIVO'],
    'CAIRO KOGUISHI' => ['admission_date' => '2025-01-20', 'position' => 'CHEFE DE DIVISÃO DE ALIMENTAÇÃO E NUTRIÇÃO'],
    'CAROLINE DEL ANHOL CAETANO' => ['admission_date' => '2025-10-07', 'position' => 'AGENTE ADMINISTRATIVO'],
    'EDSON KAZUO AMORIM INOUE' => ['admission_date' => '2023-03-01', 'position' => 'ENGENHEIRO CIVIL'],
    'EDUARDO ARAUJO BICHELINE' => ['admission_date' => '2025-10-07', 'position' => 'AGENTE ADMINISTRATIVO'],
    'EVELIZE REGINA AIDA' => ['admission_date' => '2024-01-10', 'position' => 'PROFESSOR'],
    'EVERTON DOS SANTOS MORAES' => ['admission_date' => '2023-02-13', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
    'GEORGE TOSHIAKI HARA' => ['admission_date' => '2025-01-20', 'position' => 'DIRETOR DO DEPTO DE SEGURANÇA ALIMENTAR E NUTRICIONAL'],
    'KARINA AUGUSTO DOS SANTOS' => ['admission_date' => '2024-01-10', 'position' => 'PROFESSOR'],
    'MARIA IZABEL FERNANDES MARTINS' => ['admission_date' => '2024-01-10', 'position' => 'PROFESSOR'],
    'MICHEL ANGELO BOMTEMPO' => ['admission_date' => '2025-01-01', 'position' => 'CHEFE DE GABINETE DO PREFEITO'],
    'ODAIR DE ASSIS E SILVA' => ['admission_date' => '2022-06-01', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
    'PAULO ROBERTO MOREIRA' => ['admission_date' => '2021-01-04', 'position' => 'COORDENADOR DE CONTROLE INTERNO'],
    'PEDRO ELIAS BARBOSA NETO' => ['admission_date' => '2022-07-14', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
    'RAFAEL GOUVEIA GRECA' => ['admission_date' => '2025-01-01', 'position' => 'DIRETOR DO DEPARTAMENTO DE COMUNICAÇÃO'],
    'ROSANIA APARECIDA DA SILVA NAKANO' => ['admission_date' => '2024-01-10', 'position' => 'PROFESSOR'],
    'WILSON HIROSHI HASHIMOTO' => ['admission_date' => '2023-02-13', 'position' => 'AGENTE DE SERVIÇOS OPERACIONAIS'],
];

echo "=== CORREÇÃO COMPLEMENTAR ===\n\n";

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
foreach ($missingEmployees as $name => $data) {
    $nameUpper = strtoupper(trim($name));
    
    // Encontrar person_id
    $personId = $nameToPersonId[$nameUpper] ?? null;
    
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
        // Tentar padrão alternativo (pode já ter position preenchida ou formato diferente)
        $pattern2 = "/(\['id' => \d+, 'person_id' => $personId, [^]]*'admission_date' => )'2020-01-01'/";
        $replacement2 = "\${1}'{$data['admission_date']}'";
        $newContent = preg_replace($pattern2, $replacement2, $seederContent);
        
        if ($newContent !== $seederContent) {
            $seederContent = $newContent;
            $updates++;
            echo "✓ Atualizado (só data): $name (person_id: $personId)\n";
        } else {
            echo "AVISO: Não foi possível atualizar '$name' (person_id: $personId)\n";
        }
    }
}

// Salvar
file_put_contents($seederPath, $seederContent);

echo "\n=== RESULTADO ===\n";
echo "Total de atualizações: $updates\n";

// Verificar quantos ainda faltam
preg_match_all("/admission_date' => '2020-01-01'/", $seederContent, $remaining);
echo "Registros ainda com placeholder: " . count($remaining[0]) . "\n";
