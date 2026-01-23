<?php
/**
 * Script para atualizar o ProductionDataSeeder.php com os dados corretos
 * Este script substitui COMPLETAMENTE as funções getPeopleData() e getEmployeeRegistrationsData()
 */

$seederPath = __DIR__ . '/../database/seeders/ProductionDataSeeder.php';
$peopleDataPath = __DIR__ . '/seeder_output/people_data_final.php';
$registrationsDataPath = __DIR__ . '/seeder_output/registrations_data_final.php';

echo "=== ATUALIZANDO ProductionDataSeeder.php ===\n\n";

// Lê o arquivo original
$originalContent = file_get_contents($seederPath);
echo "1. Arquivo original: " . strlen($originalContent) . " bytes\n";

// Lê os novos dados (remove o "<?php\n\n" do início)
$newPeopleData = trim(preg_replace('/^<\?php\s*/', '', file_get_contents($peopleDataPath)));
$newRegistrationsData = trim(preg_replace('/^<\?php\s*/', '', file_get_contents($registrationsDataPath)));

echo "2. Novo getPeopleData: " . strlen($newPeopleData) . " bytes\n";
echo "3. Novo getEmployeeRegistrationsData: " . strlen($newRegistrationsData) . " bytes\n";

// Encontra as posições das funções
$lines = explode("\n", $originalContent);
$totalLines = count($lines);

// Encontrar início e fim de getPeopleData()
$peopleDataStart = null;
$peopleDataEnd = null;
$regsDataStart = null;
$regsDataEnd = null;
$workShiftStart = null;

for ($i = 0; $i < $totalLines; $i++) {
    $line = $lines[$i];
    
    if (strpos($line, 'private function getPeopleData()') !== false) {
        $peopleDataStart = $i;
    }
    if (strpos($line, 'private function getEmployeeRegistrationsData()') !== false) {
        $regsDataStart = $i;
        if ($peopleDataStart !== null) {
            // O fim de getPeopleData é a linha anterior (buscando o fechamento })
            for ($j = $i - 1; $j > $peopleDataStart; $j--) {
                if (trim($lines[$j]) === '}') {
                    $peopleDataEnd = $j;
                    break;
                }
            }
        }
    }
    if (strpos($line, 'private function getWorkShiftTemplatesData()') !== false) {
        $workShiftStart = $i;
        if ($regsDataStart !== null) {
            // O fim de getEmployeeRegistrationsData é a linha anterior (buscando o fechamento })
            for ($j = $i - 1; $j > $regsDataStart; $j--) {
                if (trim($lines[$j]) === '}') {
                    $regsDataEnd = $j;
                    break;
                }
            }
        }
    }
}

echo "4. getPeopleData: linhas $peopleDataStart até $peopleDataEnd\n";
echo "5. getEmployeeRegistrationsData: linhas $regsDataStart até $regsDataEnd\n";
echo "6. getWorkShiftTemplatesData começa na linha: $workShiftStart\n";

if ($peopleDataStart === null || $peopleDataEnd === null || $regsDataStart === null || $regsDataEnd === null) {
    die("ERRO: Não foi possível encontrar as funções no arquivo\n");
}

// Monta o novo conteúdo
$newLines = [];

// Parte 1: Antes de getPeopleData()
for ($i = 0; $i < $peopleDataStart; $i++) {
    $newLines[] = $lines[$i];
}

// Parte 2: Nova função getPeopleData()
$newLines[] = $newPeopleData;
$newLines[] = '';

// Parte 3: Nova função getEmployeeRegistrationsData()
$newLines[] = $newRegistrationsData;
$newLines[] = '';

// Parte 4: A partir de getWorkShiftTemplatesData() até o final
for ($i = $workShiftStart; $i < $totalLines; $i++) {
    $newLines[] = $lines[$i];
}

$newContent = implode("\n", $newLines);

// Cria backup
$backupPath = $seederPath . '.backup_' . date('Ymd_His');
copy($seederPath, $backupPath);
echo "7. Backup criado: $backupPath\n";

// Salva o novo arquivo
file_put_contents($seederPath, $newContent);
echo "8. Arquivo atualizado: " . strlen($newContent) . " bytes\n";

echo "\n=== ATUALIZAÇÃO CONCLUÍDA ===\n";
echo "\nPróximo passo: Execute 'php artisan migrate:fresh --seed'\n";
