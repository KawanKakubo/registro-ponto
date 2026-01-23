<?php
/**
 * Script para verificar se colaboradores sem jornada ativa estão no arquivo de jornadas
 */

// Colaboradores reportados sem jornada ativa
$colaboradoresSemJornada = [
    'ADENILSON WAGNER FELIPE' => ['matricula' => '1537', 'cpf' => '021.908.359-25', 'pis' => '170.57798.87-1'],
    'ADRIANO LACERDA DA SILVA' => ['matricula' => '3474', 'cpf' => '068.882.209-66', 'pis' => '160.01659.06-1'],
    'ALEXANDRE CARLOS DA SILVA' => ['matricula' => '3431', 'cpf' => '074.950.889-23', 'pis' => '201.07813.30-5'],
    'ANADELIA ROSA GOUVEIA HASHIMOTO' => ['matricula' => '1674', 'cpf' => '847.169.679-72', 'pis' => '170.48137.88-4'],
    'ANGELA MIDORI GOTO' => ['matricula' => '1383', 'cpf' => '020.257.059-21', 'pis' => '170.46226.85-5'],
    'ANTÔNIO SANTANA CECÍLIO' => ['matricula' => '1903', 'cpf' => '534.794.329-72', 'pis' => '200.88809.85-9'],
    'BIANCA ARAÚJO DA SILVA' => ['matricula' => '1724', 'cpf' => '054.630.029-40', 'pis' => '160.06976.53-7'],
    'CLEUNICE MENEZES SOUTO DA SILVA' => ['matricula' => '1461', 'cpf' => '025.930.739-42', 'pis' => '170.48138.39-2'],
    'CRISTIANI FERNANDES MIGUEL LOPES' => ['matricula' => '3073', 'cpf' => '065.617.659-82', 'pis' => '130.66357.50-2'],
    'CRIZELENE DA SILVA CHAGAS' => ['matricula' => '3504', 'cpf' => '009.950.919-94', 'pis' => '163.87104.73-5'],
    'EDUARDO NÓBREGA SIMÕES' => ['matricula' => '3396', 'cpf' => '052.300.359-51', 'pis' => '200.50673.84-4'],
    'ELAINE CRISTINA BRAGA' => ['matricula' => '4006', 'cpf' => '018.291.389-95', 'pis' => '125.45190.07-3'],
    'ELIANA ALVES DE SOUZA' => ['matricula' => '1121', 'cpf' => '757.356.289-68', 'pis' => '170.36396.66-9'],
    'FABIO LOURENÇO DA SILVA' => ['matricula' => '3712', 'cpf' => '049.021.009-08', 'pis' => '128.64084.49-1'],
    'FLORISNI RODRIGUES DOS SANTOS LIMA' => ['matricula' => '3193', 'cpf' => '769.116.089-53', 'pis' => '129.81620.49-7'],
    'GIOVANNA LEANDRO DA SILVA CHEHADE' => ['matricula' => '3931', 'cpf' => '076.583.229-11', 'pis' => '204.14058.86-5'],
    'GISELE MIYUKI TSUDA SHINOHATA' => ['matricula' => '3698', 'cpf' => '061.032.999-50', 'pis' => '212.86089.50-8'],
    'JOSEANE DOS SANTOS GASPARI' => ['matricula' => '3940', 'cpf' => '107.230.789-80', 'pis' => '134.47975.57-0'],
    'MÁRCIO FRANCISCO SOARES' => ['matricula' => '3006', 'cpf' => '579.126.109-87', 'pis' => '170.15931.79-4'],
    'MARCOS ANTÔNIO SILVESTRE' => ['matricula' => '3778', 'cpf' => '030.767.469-01', 'pis' => '127.91651.49-9'],
    'MARIA ONDINA RODRIGUES IZU' => ['matricula' => '3676', 'cpf' => '362.837.979-20', 'pis' => '108.43914.22-7'],
    'NATÁLIA TATEWAKI' => ['matricula' => '1617', 'cpf' => '031.496.269-78', 'pis' => '128.13515.51-7'],
    'RÚBIA RIBEIRO DE ASSIS' => ['matricula' => '3227', 'cpf' => '024.453.549-35', 'pis' => '126.00223.51-9'],
    'SIDINEI DOS SANTOS LOPES' => ['matricula' => '3399', 'cpf' => '051.745.879-90', 'pis' => '165.65572.16-0'],
    'THAÍS DUTRA DA HORA' => ['matricula' => '3833', 'cpf' => '094.008.869-00', 'pis' => '165.39169.61-3'],
    'THAYANE FRANCE PEREIRA' => ['matricula' => '3217', 'cpf' => '091.218.829-40', 'pis' => '207.63443.31-4'],
    'VITORIA EMANUELLY SILVA' => ['matricula' => '4018', 'cpf' => '091.664.239-96', 'pis' => '166.01802.20-5'],
    'WALMIR DA SILVA MATOS' => ['matricula' => '3345', 'cpf' => '', 'pis' => '108.12147.14-3'],
    'WANDERLEIA SUEIRO' => ['matricula' => '1220', 'cpf' => '', 'pis' => '170.46226.65-0'],
    'WANDERLEY BOSELY DANTAS' => ['matricula' => '1606', 'cpf' => '', 'pis' => '170.01000.68-8'],
    'WANDERLEY FERREIRA KOJO' => ['matricula' => '3342', 'cpf' => '', 'pis' => '102.47043.50-5'],
    'WELLINTON LOPES NOVAES' => ['matricula' => '1855', 'cpf' => '', 'pis' => '130.67023.50-0'],
    'WILIAN DA SILVA BENEVIDES' => ['matricula' => '3505', 'cpf' => '', 'pis' => '166.08623.79-9'],
    'WILSON BRIZOLA' => ['matricula' => '3813', 'cpf' => '', 'pis' => '121.78474.98-7'],
    'WILSON LAUREANO' => ['matricula' => '1149', 'cpf' => '', 'pis' => '120.13349.19-1'],
    'YOSHIKAZU UNO' => ['matricula' => '3565', 'cpf' => '', 'pis' => '102.59369.10-9'],
    'ZILÁ RUFINO DA SILVA' => ['matricula' => '12094989676', 'cpf' => '', 'pis' => '120.94989.67-6'],
    'ZILDA APARECIDA PAZ' => ['matricula' => '1133', 'cpf' => '', 'pis' => '170.36396.76-6'],
    'ZILDA APARECIDA RODRIGUES' => ['matricula' => '3520', 'cpf' => '', 'pis' => '170.23881.84-9'],
    'ZOELI GIL CELESTINO' => ['matricula' => '795', 'cpf' => '', 'pis' => '170.23880.36-2'],
];

// Ler arquivo de jornadas
$arquivoJornadas = '/home/kawan/Downloads/LISTAGEM DE FUNCIONÁRIOS.csv';
$conteudo = file_get_contents($arquivoJornadas);

// Normalizar nomes (remover acentos e converter para maiúsculas)
function normalizar($texto) {
    $texto = mb_strtoupper($texto, 'UTF-8');
    $texto = preg_replace(
        ['/[ÀÁÂÃÄÅ]/', '/[ÈÉÊË]/', '/[ÌÍÎÏ]/', '/[ÒÓÔÕÖ]/', '/[ÙÚÛÜ]/', '/[Ç]/', '/[Ñ]/'],
        ['A', 'E', 'I', 'O', 'U', 'C', 'N'],
        $texto
    );
    return trim($texto);
}

// Limpar PIS (remover pontuação)
function limparPis($pis) {
    return preg_replace('/[^0-9]/', '', $pis);
}

echo "=" . str_repeat("=", 79) . "\n";
echo "VERIFICAÇÃO DE COLABORADORES SEM JORNADA ATIVA\n";
echo "=" . str_repeat("=", 79) . "\n\n";

$encontrados = [];
$naoEncontrados = [];

foreach ($colaboradoresSemJornada as $nome => $dados) {
    $nomeNormalizado = normalizar($nome);
    $matricula = $dados['matricula'];
    $pisLimpo = limparPis($dados['pis']);
    
    $encontradoPorNome = false;
    $encontradoPorMatricula = false;
    $encontradoPorPis = false;
    $horarioEncontrado = '';
    $departamentoEncontrado = '';
    
    // Verificar por nome
    if (stripos($conteudo, $nome) !== false || stripos($conteudo, $nomeNormalizado) !== false) {
        $encontradoPorNome = true;
    }
    
    // Verificar por matrícula (buscar padrão ",MATRICULA," ou "MATRICULA,")
    if (preg_match('/[,\s]' . preg_quote($matricula, '/') . '[,\s]/', $conteudo)) {
        $encontradoPorMatricula = true;
    }
    
    // Verificar por PIS
    if (!empty($pisLimpo) && strpos($conteudo, $pisLimpo) !== false) {
        $encontradoPorPis = true;
    }
    
    // Extrair horário e departamento se encontrado
    if ($encontradoPorNome || $encontradoPorPis) {
        // Buscar linha que contém o nome ou PIS
        if (preg_match('/.*' . preg_quote($pisLimpo, '/') . '.*?(\d+\s*-\s*[^,]+).*?,([^,]+),\d{2}\/\d{2}\/\d{2,4}/i', $conteudo, $matches)) {
            $horarioEncontrado = trim($matches[1]);
            $departamentoEncontrado = trim($matches[2]);
        }
    }
    
    if ($encontradoPorNome || $encontradoPorMatricula || $encontradoPorPis) {
        $encontrados[$nome] = [
            'por_nome' => $encontradoPorNome,
            'por_matricula' => $encontradoPorMatricula,
            'por_pis' => $encontradoPorPis,
            'horario' => $horarioEncontrado,
            'departamento' => $departamentoEncontrado,
            'dados' => $dados
        ];
    } else {
        $naoEncontrados[$nome] = $dados;
    }
}

echo "📊 RESUMO:\n";
echo "   - Total verificados: " . count($colaboradoresSemJornada) . "\n";
echo "   - Encontrados no arquivo: " . count($encontrados) . "\n";
echo "   - NÃO encontrados: " . count($naoEncontrados) . "\n\n";

if (!empty($encontrados)) {
    echo "✅ COLABORADORES ENCONTRADOS NO ARQUIVO DE JORNADAS:\n";
    echo "-" . str_repeat("-", 79) . "\n";
    
    foreach ($encontrados as $nome => $info) {
        echo "\n   📌 $nome\n";
        echo "      Matrícula: {$info['dados']['matricula']}\n";
        echo "      PIS: {$info['dados']['pis']}\n";
        
        $metodos = [];
        if ($info['por_nome']) $metodos[] = 'Nome';
        if ($info['por_matricula']) $metodos[] = 'Matrícula';
        if ($info['por_pis']) $metodos[] = 'PIS';
        echo "      Encontrado por: " . implode(', ', $metodos) . "\n";
        
        if (!empty($info['horario'])) {
            echo "      Horário no arquivo: {$info['horario']}\n";
        }
        if (!empty($info['departamento'])) {
            echo "      Departamento no arquivo: {$info['departamento']}\n";
        }
    }
}

if (!empty($naoEncontrados)) {
    echo "\n\n❌ COLABORADORES NÃO ENCONTRADOS NO ARQUIVO DE JORNADAS:\n";
    echo "-" . str_repeat("-", 79) . "\n";
    
    foreach ($naoEncontrados as $nome => $dados) {
        echo "\n   ⚠️  $nome\n";
        echo "      Matrícula: {$dados['matricula']}\n";
        if (!empty($dados['cpf'])) echo "      CPF: {$dados['cpf']}\n";
        echo "      PIS: {$dados['pis']}\n";
    }
}

echo "\n\n" . "=" . str_repeat("=", 79) . "\n";
echo "FIM DA VERIFICAÇÃO\n";
echo "=" . str_repeat("=", 79) . "\n";
