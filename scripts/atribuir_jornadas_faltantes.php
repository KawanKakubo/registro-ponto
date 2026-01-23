<?php
/**
 * Script para atribuir jornadas aos funcionários sem jornada ativa
 * 
 * Este script:
 * 1. Lê o arquivo de listagem de funcionários com horários
 * 2. Busca no banco quais funcionários estão sem jornada ativa
 * 3. Cria as atribuições de jornada correspondentes
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeeRegistration;
use App\Models\EmployeeWorkShiftAssignment;
use App\Models\WorkShiftTemplate;
use Illuminate\Support\Facades\DB;

$csvListagemPath = '/home/kawan/Downloads/LISTAGEM DE FUNCIONÁRIOS.csv';

echo "================================================================================\n";
echo "ATRIBUIÇÃO DE JORNADAS PARA FUNCIONÁRIOS SEM JORNADA ATIVA\n";
echo "================================================================================\n\n";

// ========================================
// PASSO 1: Buscar funcionários sem jornada ativa
// ========================================
echo "📊 Buscando funcionários sem jornada ativa...\n";

$registracoesBase = EmployeeRegistration::whereDoesntHave('workShiftAssignments')
    ->where('status', 'active')
    ->with('person')
    ->get();

echo "   Encontrados: " . $registracoesBase->count() . " vínculos sem jornada ativa\n\n";

if ($registracoesBase->isEmpty()) {
    echo "✅ Todos os funcionários já têm jornada atribuída!\n";
    exit(0);
}

// ========================================
// PASSO 2: Ler arquivo de listagem para obter horários
// ========================================
echo "📂 Lendo arquivo de listagem de funcionários...\n";

$content = file_get_contents($csvListagemPath);
$content = mb_convert_encoding($content, 'UTF-8', 'auto');
$lines = explode("\n", $content);

$dadosPorPis = [];
$dadosPorMatricula = [];
$dadosPorNome = [];

$isFirstLine = true;
foreach ($lines as $line) {
    if ($isFirstLine) {
        $isFirstLine = false;
        continue;
    }
    
    $line = trim($line);
    if (empty($line)) continue;
    
    $cols = str_getcsv($line, ',');
    if (count($cols) < 8) continue;
    
    $nFolha = trim($cols[0] ?? '');
    $pis = preg_replace('/[^0-9]/', '', $cols[1] ?? '');
    $nome = trim($cols[2] ?? '');
    $matricula = trim($cols[3] ?? '');
    $horario = trim($cols[4] ?? '');
    $cargo = trim($cols[5] ?? '');
    $departamento = trim($cols[6] ?? '');
    $dataAdmissao = trim($cols[7] ?? '');
    
    if (empty($matricula)) {
        $matricula = $nFolha;
    }
    
    $registro = [
        'matricula' => $matricula,
        'nome' => mb_strtoupper($nome, 'UTF-8'),
        'pis' => $pis,
        'horario' => $horario,
        'cargo' => $cargo,
        'departamento' => $departamento,
        'data_admissao' => $dataAdmissao,
    ];
    
    if (!empty($pis)) {
        $dadosPorPis[$pis] = $registro;
    }
    if (!empty($matricula)) {
        $dadosPorMatricula[$matricula] = $registro;
    }
    if (!empty($nome)) {
        $nomeNorm = mb_strtoupper(trim($nome), 'UTF-8');
        $dadosPorNome[$nomeNorm] = $registro;
    }
}

echo "   Lidos: " . count($dadosPorMatricula) . " funcionários por matrícula\n";
echo "   Lidos: " . count($dadosPorPis) . " funcionários por PIS\n\n";

// ========================================
// PASSO 3: Mapear códigos de horário para template_id
// Os códigos no CSV correspondem DIRETAMENTE aos IDs dos templates!
// Ex: "7 - SAÚDE -07:30-11:30 E 13:00-17:00" -> template_id = 7
// ========================================
echo "📋 Carregando templates de jornada existentes...\n";

$templates = WorkShiftTemplate::all();
$templatesExistentes = [];

foreach ($templates as $template) {
    $templatesExistentes[$template->id] = $template->name;
}

echo "   Templates existentes: " . count($templatesExistentes) . "\n\n";

// ========================================
// PASSO 4: Buscar jornada para cada funcionário sem jornada
// ========================================
echo "🔍 Buscando jornadas para funcionários sem jornada...\n\n";

$encontrados = [];
$naoEncontrados = [];

foreach ($registracoesBase as $reg) {
    $pisLimpo = preg_replace('/[^0-9]/', '', $reg->person->pis_pasep ?? '');
    $matricula = $reg->matricula;
    $nome = mb_strtoupper($reg->person->full_name, 'UTF-8');
    
    $dadosListagem = null;
    $metodo = '';
    
    // Buscar por PIS
    if (!empty($pisLimpo) && isset($dadosPorPis[$pisLimpo])) {
        $dadosListagem = $dadosPorPis[$pisLimpo];
        $metodo = 'PIS';
    }
    // Buscar por matrícula
    elseif (isset($dadosPorMatricula[$matricula])) {
        $dadosListagem = $dadosPorMatricula[$matricula];
        $metodo = 'Matrícula';
    }
    // Buscar por nome (normalizado)
    else {
        foreach ($dadosPorNome as $nomeListagem => $dados) {
            if ($nomeListagem === $nome || 
                strpos($nomeListagem, $nome) !== false || 
                strpos($nome, $nomeListagem) !== false) {
                $dadosListagem = $dados;
                $metodo = 'Nome';
                break;
            }
        }
    }
    
    if ($dadosListagem && !empty($dadosListagem['horario'])) {
        // Extrair código do horário - este código É o template_id!
        if (preg_match('/^(\d+)\s*-/', $dadosListagem['horario'], $matches)) {
            $templateId = (int)$matches[1];
            
            if (isset($templatesExistentes[$templateId])) {
                $encontrados[] = [
                    'registration' => $reg,
                    'template_id' => $templateId,
                    'codigo_horario' => $templateId,
                    'horario_texto' => $dadosListagem['horario'],
                    'template_nome' => $templatesExistentes[$templateId],
                    'metodo' => $metodo,
                    'data_admissao' => $reg->admission_date,
                ];
            } else {
                $naoEncontrados[] = [
                    'registration' => $reg,
                    'motivo' => "Template ID não existe: $templateId",
                    'horario_texto' => $dadosListagem['horario'],
                    'metodo' => $metodo,
                ];
            }
        } else {
            $naoEncontrados[] = [
                'registration' => $reg,
                'motivo' => 'Não foi possível extrair código do horário',
                'horario_texto' => $dadosListagem['horario'],
                'metodo' => $metodo,
            ];
        }
    } else {
        $naoEncontrados[] = [
            'registration' => $reg,
            'motivo' => 'Não encontrado na listagem',
            'horario_texto' => '',
            'metodo' => '',
        ];
    }
}

echo "📊 RESUMO:\n";
echo "   - Jornadas encontradas: " . count($encontrados) . "\n";
echo "   - Jornadas não encontradas: " . count($naoEncontrados) . "\n\n";

// ========================================
// PASSO 5: Mostrar detalhes dos encontrados
// ========================================
if (!empty($encontrados)) {
    echo "✅ FUNCIONÁRIOS COM JORNADA IDENTIFICADA:\n";
    echo "-" . str_repeat("-", 79) . "\n";
    
    foreach ($encontrados as $item) {
        $reg = $item['registration'];
        echo "   📌 {$reg->person->full_name}\n";
        echo "      Matrícula: {$reg->matricula}\n";
        echo "      Template ID: {$item['template_id']}\n";
        echo "      Template: {$item['template_nome']}\n";
        echo "      Horário CSV: {$item['horario_texto']}\n";
        echo "      Encontrado por: {$item['metodo']}\n\n";
    }
}

if (!empty($naoEncontrados)) {
    echo "\n❌ FUNCIONÁRIOS SEM JORNADA IDENTIFICADA:\n";
    echo "-" . str_repeat("-", 79) . "\n";
    
    foreach ($naoEncontrados as $item) {
        $reg = $item['registration'];
        echo "   ⚠️  {$reg->person->full_name}\n";
        echo "      Matrícula: {$reg->matricula}\n";
        echo "      Motivo: {$item['motivo']}\n";
        if (!empty($item['horario_texto'])) {
            echo "      Horário encontrado: {$item['horario_texto']}\n";
        }
        echo "\n";
    }
}

// ========================================
// PASSO 6: Perguntar se deseja criar as atribuições
// ========================================
if (empty($encontrados)) {
    echo "\n⚠️  Nenhuma jornada foi identificada para criar atribuições.\n";
    exit(0);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "🔧 CRIAR ATRIBUIÇÕES DE JORNADA?\n";
echo str_repeat("=", 80) . "\n";
echo "\nSerão criadas " . count($encontrados) . " atribuições de jornada.\n";
echo "Deseja continuar? (s/n): ";

$handle = fopen("php://stdin", "r");
$resposta = trim(fgets($handle));

if (strtolower($resposta) !== 's') {
    echo "\n❌ Operação cancelada.\n";
    exit(0);
}

// ========================================
// PASSO 7: Criar as atribuições
// ========================================
echo "\n🔧 Criando atribuições de jornada...\n";

$criadas = 0;
$erros = [];

DB::beginTransaction();

try {
    foreach ($encontrados as $item) {
        $reg = $item['registration'];
        
        // Calcular data de início (usar data de admissão ou data atual)
        $effectiveFrom = $item['data_admissao'] ?? now()->format('Y-m-d');
        
        // Criar atribuição
        EmployeeWorkShiftAssignment::create([
            'employee_registration_id' => $reg->id,
            'template_id' => $item['template_id'],
            'effective_from' => $effectiveFrom,
            'assigned_by' => 1, // Admin
            'assigned_at' => now(),
        ]);
        
        $criadas++;
        echo "   ✓ {$reg->person->full_name} (Mat: {$reg->matricula}) -> Template {$item['template_id']}\n";
    }
    
    DB::commit();
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ CONCLUÍDO!\n";
    echo str_repeat("=", 80) . "\n";
    echo "   - Atribuições criadas: $criadas\n";
    echo "   - Erros: " . count($erros) . "\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERRO: {$e->getMessage()}\n";
    exit(1);
}
