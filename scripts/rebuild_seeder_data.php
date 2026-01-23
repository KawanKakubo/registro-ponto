<?php
/**
 * Script para reconstruir os dados do ProductionDataSeeder
 * Extrai dados dos 2 CSVs e gera arrays PHP corretos
 */

// Caminho para os CSVs
$csv1Path = __DIR__ . '/../LISTAGEM DE FUNCIONÁRIOS.csv';
$csv2Path = __DIR__ . '/../Listagem de funcionários - dinâmica(1).csv';

echo "=== RECONSTRUÇÃO DO SEEDER ===\n\n";

// Função para normalizar nomes (remover acentos, uppercase, espaços extras)
function normalizeName($name) {
    $name = mb_strtoupper(trim($name));
    $name = preg_replace('/\s+/', ' ', $name);
    
    // Remover acentos
    $acentos = [
        'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ç' => 'C', 'Ñ' => 'N',
    ];
    $name = strtr($name, $acentos);
    
    return $name;
}

// 1. Ler o primeiro CSV (LISTAGEM DE FUNCIONÁRIOS.csv)
// Formato: Nº FOLHA, Nº PIS/PASEP, NOME, Nº IDENTIFICADOR, HORÁRIO, FUNÇÃO, DEPARTAMENTO, ADMISSÃO, EMPRESA
echo "Lendo CSV 1: LISTAGEM DE FUNCIONÁRIOS.csv...\n";
$csv1Data = [];
$csv1ByMatricula = [];
$handle1 = fopen($csv1Path, 'r');
$isFirst = true;
while (($row = fgetcsv($handle1)) !== false) {
    if ($isFirst) {
        $isFirst = false;
        continue; // Pular cabeçalho
    }
    
    if (count($row) < 9) continue;
    
    $folha = trim($row[0]);
    $pis = trim($row[1]);
    $nome = trim($row[2]);
    $identificador = trim($row[3]);
    $horario = trim($row[4]);
    $funcao = trim($row[5]);
    $departamento = trim($row[6]);
    $admissao = trim($row[7]);
    $empresa = trim($row[8]);
    
    if (empty($nome)) continue;
    
    // Usar nome normalizado como chave
    $nomeKey = normalizeName($nome);
    
    // Determinar matrícula: pode ser folha ou identificador
    $matricula = !empty($identificador) ? $identificador : $folha;
    
    $record = [
        'folha' => $folha,
        'pis_csv1' => $pis,
        'nome' => $nome,
        'nome_normalizado' => $nomeKey,
        'matricula' => $matricula,
        'horario' => $horario,
        'funcao' => $funcao,
        'departamento' => $departamento,
        'admissao' => $admissao,
    ];
    
    $csv1Data[$nomeKey] = $record;
    $csv1ByMatricula[$matricula] = $record;
}
fclose($handle1);
echo "Registros no CSV1: " . count($csv1Data) . "\n\n";

// 2. Ler o segundo CSV (Listagem de funcionários - dinâmica)
// Formato complexo - precisa encontrar linhas com matrícula/CPF/PIS
echo "Lendo CSV 2: Listagem de funcionários - dinâmica(1).csv...\n";
$csv2Data = [];
$csv2ByName = [];
$handle2 = fopen($csv2Path, 'r');
$lineNum = 0;
while (($line = fgets($handle2)) !== false) {
    $lineNum++;
    
    // Linhas de dados têm formato: ,MATRICULA/0,,,,,NOME,,CPF,PIS,DATA,
    // Ex: ,1484/0,,,,,CLAUDINEIA DOS SANTOS,,832.099.809-34  ,12435614456,01/07/1998,
    
    $row = str_getcsv($line);
    if (count($row) < 11) continue;
    
    // Verificar se tem matrícula no formato XXXX/0
    $matriculaField = trim($row[1] ?? '');
    if (!preg_match('/^\d+\/\d+$/', $matriculaField)) continue;
    
    // Extrair matrícula sem o /0
    $matricula = preg_replace('/\/\d+$/', '', $matriculaField);
    
    $nome = trim($row[6] ?? '');
    $cpf = trim(preg_replace('/\s+/', '', $row[8] ?? ''));
    $pis = trim($row[9] ?? '');
    $admissao = trim($row[10] ?? '');
    
    if (empty($matricula) || empty($cpf)) continue;
    
    // Remover formatação do CPF para limpar
    $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpfLimpo) != 11) continue;
    
    // Normalizar nome para fazer match
    $nomeKey = normalizeName($nome);
    
    $record = [
        'matricula' => $matricula,
        'nome' => $nome,
        'nome_key' => $nomeKey,
        'cpf' => $cpf,
        'cpf_limpo' => $cpfLimpo,
        'pis' => $pis,
        'admissao' => $admissao,
    ];
    
    $csv2Data[$matricula] = $record;
    
    // Indexar também por nome (pode haver múltiplos registros por pessoa)
    if (!isset($csv2ByName[$nomeKey])) {
        $csv2ByName[$nomeKey] = [];
    }
    $csv2ByName[$nomeKey][] = $record;
}
fclose($handle2);
echo "Registros no CSV2: " . count($csv2Data) . "\n\n";

// 3. Função para encontrar CPF/PIS por diferentes métodos de matching
function findCpfData($data1, $csv2Data, $csv2ByName) {
    $matricula = $data1['matricula'];
    $nomeKey = $data1['nome_normalizado'];
    
    // Método 1: Match exato por matrícula
    if (isset($csv2Data[$matricula])) {
        return $csv2Data[$matricula];
    }
    
    // Método 2: Match exato por nome normalizado
    if (isset($csv2ByName[$nomeKey])) {
        return $csv2ByName[$nomeKey][0];
    }
    
    // Método 3: Match por PIS (folha pode ser PIS no CSV1)
    $folha = $data1['folha'];
    $pisCsv1 = $data1['pis_csv1'];
    foreach ($csv2Data as $record) {
        if ($record['pis'] === $folha || $record['pis'] === $pisCsv1) {
            return $record;
        }
    }
    
    // Método 4: Match parcial de nome (primeiros 2-3 componentes)
    $partes1 = explode(' ', $nomeKey);
    foreach ($csv2ByName as $nome2 => $items) {
        $partes2 = explode(' ', $nome2);
        
        // Comparar primeiro e segundo nome
        if (count($partes1) >= 2 && count($partes2) >= 2) {
            if ($partes1[0] === $partes2[0] && $partes1[1] === $partes2[1]) {
                // Verificar se terceiro nome também bate (se existir)
                if (count($partes1) >= 3 && count($partes2) >= 3) {
                    if ($partes1[2] === $partes2[2]) {
                        return $items[0];
                    }
                } else {
                    return $items[0];
                }
            }
        }
    }
    
    // Método 5: Match fuzzy - nome contém ou está contido
    foreach ($csv2ByName as $nome2 => $items) {
        // Remove preposições comuns para comparar
        $n1 = str_replace([' DE ', ' DA ', ' DO ', ' DOS ', ' DAS '], ' ', $nomeKey);
        $n2 = str_replace([' DE ', ' DA ', ' DO ', ' DOS ', ' DAS '], ' ', $nome2);
        
        if (strlen($n1) > 10 && strlen($n2) > 10) {
            // Se os primeiros 15 caracteres batem
            if (substr($n1, 0, 15) === substr($n2, 0, 15)) {
                return $items[0];
            }
        }
    }
    
    return null;
}

// 4. Combinar dados dos 2 CSVs
echo "Combinando dados...\n";
$combinedData = [];
$semCpf = [];
$comCpf = 0;

foreach ($csv1Data as $nomeKey => $data1) {
    $match = findCpfData($data1, $csv2Data, $csv2ByName);
    
    $cpf = null;
    $pis = $data1['pis_csv1'];
    
    if ($match) {
        $cpf = $match['cpf'];
        $pis = $match['pis'] ?: $pis;
        $comCpf++;
    } else {
        $semCpf[] = $data1['nome'];
    }
    
    $combinedData[] = [
        'nome' => $data1['nome'],
        'cpf' => $cpf,
        'pis' => $pis,
        'matricula' => $data1['matricula'],
        'funcao' => $data1['funcao'],
        'departamento' => $data1['departamento'],
        'admissao' => $data1['admissao'],
    ];
}

echo "Colaboradores com CPF: $comCpf\n";
echo "Colaboradores sem CPF: " . count($semCpf) . "\n\n";

if (count($semCpf) > 0 && count($semCpf) <= 50) {
    echo "Nomes sem CPF encontrado:\n";
    foreach ($semCpf as $nome) {
        echo "  - $nome\n";
    }
    echo "\n";
}

// 5. Gerar código PHP para o seeder
echo "Gerando código do seeder...\n\n";

// 5.1 Gerar getPeopleData()
$peopleCode = "    private function getPeopleData(): array\n    {\n        return [\n";
foreach ($combinedData as $person) {
    $nome = addslashes($person['nome']);
    $cpf = $person['cpf'] ? "'" . addslashes($person['cpf']) . "'" : 'null';
    $pis = $person['pis'] ? "'" . addslashes($person['pis']) . "'" : 'null';
    
    $peopleCode .= "            ['name' => '$nome', 'cpf' => $cpf, 'pis' => $pis],\n";
}
$peopleCode .= "        ];\n    }\n";

// 5.2 Gerar getEmployeeRegistrationsData()
$registrationsCode = "    private function getEmployeeRegistrationsData(): array\n    {\n        return [\n";
foreach ($combinedData as $person) {
    $nome = addslashes($person['nome']);
    $matricula = addslashes($person['matricula']);
    $cargo = addslashes($person['funcao']);
    $departamento = addslashes($person['departamento']);
    $admissao = addslashes($person['admissao']);
    
    $registrationsCode .= "            ['person_name' => '$nome', 'matricula' => '$matricula', 'position' => '$cargo', 'department' => '$departamento', 'admission_date' => '$admissao'],\n";
}
$registrationsCode .= "        ];\n    }\n";

// Salvar arquivos de saída
$outputPath = __DIR__ . '/seeder_output/';
if (!is_dir($outputPath)) {
    mkdir($outputPath, 0755, true);
}

file_put_contents($outputPath . 'people_data.php', "<?php\n\n" . $peopleCode);
file_put_contents($outputPath . 'registrations_data.php', "<?php\n\n" . $registrationsCode);

echo "Arquivos gerados em: $outputPath\n";
echo "  - people_data.php\n";
echo "  - registrations_data.php\n\n";

// 6. Estatísticas finais
echo "=== ESTATÍSTICAS FINAIS ===\n";
echo "Total de colaboradores: " . count($combinedData) . "\n";
echo "Com CPF: $comCpf\n";
echo "Sem CPF: " . count($semCpf) . "\n";
echo "Taxa de sucesso CPF: " . round(($comCpf / count($combinedData)) * 100, 2) . "%\n";

// 7. Listar departamentos únicos
$departamentos = array_unique(array_column($combinedData, 'departamento'));
sort($departamentos);
echo "\nDepartamentos encontrados (" . count($departamentos) . "):\n";
foreach ($departamentos as $dep) {
    if (!empty($dep)) {
        echo "  - $dep\n";
    }
}

// 8. Salvar dados combinados em JSON para referência
file_put_contents($outputPath . 'combined_data.json', json_encode($combinedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nDados combinados salvos em: combined_data.json\n";
