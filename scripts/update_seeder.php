<?php
/**
 * Script para atualizar o ProductionDataSeeder.php com os novos dados de pessoas e vínculos
 */

$seederPath = __DIR__ . '/../database/seeders/ProductionDataSeeder.php';
$peopleDataPath = __DIR__ . '/seeder_output/people_data.php';
$registrationsDataPath = __DIR__ . '/seeder_output/registrations_data.php';

echo "=== ATUALIZANDO ProductionDataSeeder.php ===\n\n";

// Lê o conteúdo atual do seeder
$seederContent = file_get_contents($seederPath);
echo "1. Seeder original: " . strlen($seederContent) . " bytes\n";

// Lê os novos dados
$peopleDataContent = file_get_contents($peopleDataPath);
$registrationsDataContent = file_get_contents($registrationsDataPath);

// Remove o "<?php\n\n" do início dos arquivos de dados
$peopleDataContent = preg_replace('/^<\?php\s+/', '', $peopleDataContent);
$registrationsDataContent = preg_replace('/^<\?php\s+/', '', $registrationsDataContent);

// Remove o fechamento da função dos dados (já está incluído no arquivo)
// Trim dos dados
$peopleDataContent = trim($peopleDataContent);
$registrationsDataContent = trim($registrationsDataContent);

echo "2. Novo getPeopleData: " . strlen($peopleDataContent) . " bytes\n";
echo "3. Novo getEmployeeRegistrationsData: " . strlen($registrationsDataContent) . " bytes\n";

// Encontra e substitui getPeopleData()
// O padrão é: private function getPeopleData(): array { ... }
// Vai até a próxima private function getEmployeeRegistrationsData()
$pattern1 = '/private function getPeopleData\(\): array\s*\{[^}]+(?:\[[^\]]*\][^}]*)+\s*\];\s*\}/s';
if (preg_match($pattern1, $seederContent)) {
    $seederContent = preg_replace($pattern1, $peopleDataContent, $seederContent, 1);
    echo "4. getPeopleData substituído com sucesso!\n";
} else {
    echo "4. ERRO: Não conseguiu encontrar getPeopleData no padrão esperado\n";
    
    // Tenta uma abordagem diferente: linha a linha
    echo "   Tentando abordagem alternativa...\n";
    
    $lines = explode("\n", $seederContent);
    $newLines = [];
    $inPeopleData = false;
    $bracketCount = 0;
    $skipUntilEmployeeReg = false;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        if (strpos($line, 'private function getPeopleData()') !== false) {
            // Encontrou o início de getPeopleData, adiciona o novo conteúdo
            $newLines[] = $peopleDataContent;
            $skipUntilEmployeeReg = true;
            continue;
        }
        
        if ($skipUntilEmployeeReg) {
            if (strpos($line, 'private function getEmployeeRegistrationsData()') !== false) {
                $skipUntilEmployeeReg = false;
                // Adiciona nova linha em branco e então a função de registrations
                $newLines[] = '';
                $newLines[] = $registrationsDataContent;
                
                // Pula até a próxima função
                while ($i < count($lines) && strpos($lines[$i], 'private function getWorkShiftTemplatesData()') === false) {
                    $i++;
                }
                $i--; // Volta uma linha para que o loop principal avance corretamente
                continue;
            }
            continue; // Pula as linhas antigas
        }
        
        $newLines[] = $line;
    }
    
    $seederContent = implode("\n", $newLines);
    echo "   Abordagem alternativa concluída\n";
}

// Agora substitui getEmployeeRegistrationsData() se ainda não foi feito
if (strpos($seederContent, $registrationsDataContent) === false) {
    $pattern2 = '/private function getEmployeeRegistrationsData\(\): array\s*\{[^}]+(?:\[[^\]]*\][^}]*)+\s*\];\s*\}/s';
    if (preg_match($pattern2, $seederContent)) {
        $seederContent = preg_replace($pattern2, $registrationsDataContent, $seederContent, 1);
        echo "5. getEmployeeRegistrationsData substituído com sucesso!\n";
    } else {
        echo "5. getEmployeeRegistrationsData já foi substituído ou não encontrado\n";
    }
}

// Salva o arquivo atualizado
$backupPath = $seederPath . '.backup_' . date('Ymd_His');
copy($seederPath, $backupPath);
echo "6. Backup criado: $backupPath\n";

file_put_contents($seederPath, $seederContent);
echo "7. Seeder atualizado: " . strlen($seederContent) . " bytes\n";

echo "\n=== ATUALIZAÇÃO CONCLUÍDA ===\n";
