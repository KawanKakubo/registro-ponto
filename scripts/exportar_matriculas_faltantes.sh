#!/bin/bash

echo "🔍 Identificando matrículas faltantes no CSV..."

cd "$(dirname "$0")/.."

# Exportar matrículas do banco
php artisan tinker --execute="
\$matriculas = App\Models\EmployeeRegistration::orderBy('matricula')->pluck('matricula');
foreach (\$matriculas as \$m) {
    echo \$m . '\n';
}
" > /tmp/matriculas_banco.txt 2>/dev/null

# Extrair matrículas do CSV
awk -F',' 'NR>1 {print $4}' importacao-colaboradores.csv | sort > /tmp/matriculas_csv.txt

# Encontrar diferenças
comm -23 <(sort /tmp/matriculas_banco.txt | grep -v '^$') <(sort /tmp/matriculas_csv.txt | grep -v '^$') > matriculas_faltantes.txt

# Contar
TOTAL_BANCO=$(sort /tmp/matriculas_banco.txt | grep -v '^$' | wc -l)
TOTAL_CSV=$(sort /tmp/matriculas_csv.txt | grep -v '^$' | wc -l)
TOTAL_FALTANTES=$(cat matriculas_faltantes.txt | wc -l)

echo ""
echo "═══════════════════════════════════════════════"
echo "📊 RESULTADO"
echo "═══════════════════════════════════════════════"
echo "Matrículas no banco:     $TOTAL_BANCO"
echo "Matrículas no CSV:       $TOTAL_CSV"
echo "Matrículas faltantes:    $TOTAL_FALTANTES"
echo ""
echo "✅ Lista exportada para: matriculas_faltantes.txt"
echo ""

if [ $TOTAL_FALTANTES -gt 0 ]; then
    echo "📋 Primeiras 10 matrículas faltantes:"
    head -10 matriculas_faltantes.txt
    
    if [ $TOTAL_FALTANTES -gt 10 ]; then
        echo "... e mais $((TOTAL_FALTANTES - 10)) matrículas"
    fi
fi

# Criar relatório detalhado
echo ""
echo "📝 Gerando relatório detalhado..."

php artisan tinker --execute="
\$faltantes = file('matriculas_faltantes.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
\$file = fopen('relatorio_colaboradores_faltantes.csv', 'w');

// Cabeçalho
fputcsv(\$file, ['matricula', 'nome', 'cpf', 'pis', 'cargo', 'departamento', 'status', 'total_registros_ponto']);

foreach (\$faltantes as \$matricula) {
    \$vinculo = App\Models\EmployeeRegistration::where('matricula', trim(\$matricula))
        ->with('person')
        ->withCount('timeRecords')
        ->first();
    
    if (\$vinculo) {
        fputcsv(\$file, [
            \$vinculo->matricula,
            \$vinculo->person->full_name ?? '',
            \$vinculo->person->cpf ?? '',
            \$vinculo->person->pis_pasep ?? '',
            \$vinculo->position ?? '',
            \$vinculo->department_id ?? '',
            \$vinculo->status,
            \$vinculo->time_records_count,
        ]);
    }
}

fclose(\$file);
echo 'Relatório detalhado salvo em: relatorio_colaboradores_faltantes.csv\n';
" 2>/dev/null

echo ""
echo "✅ Processo concluído!"
echo ""
echo "📂 Arquivos gerados:"
echo "   • matriculas_faltantes.txt (lista simples)"
echo "   • relatorio_colaboradores_faltantes.csv (relatório completo)"
