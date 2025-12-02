# 🧪 Guia de Teste - Multi-Parser AFD

## Pré-requisitos

Antes de testar, certifique-se de que:

1. ✅ Migrations foram executadas (`php artisan migrate`)
2. ✅ Há colaboradores cadastrados no sistema
3. ✅ Os colaboradores possuem:
   - CPF preenchido (para testes com DIXI)
   - PIS/PASEP preenchido (para testes com Henry SF e Prisma)
   - Matrícula preenchida (para testes com Orion 5)

## 🎯 Cenários de Teste

### 1. Teste de Detecção Automática

```php
php artisan tinker

use App\Services\AfdParserService;
use App\Models\AfdImport;

$service = new AfdParserService();

// Lista formatos suportados
$formats = $service->getSupportedFormats();
foreach ($formats as $format) {
    echo "{$format['name']}: {$format['description']}\n";
}
```

**Resultado Esperado**:
```
DIXI: Formato AFD padrão Portaria 1510/2009 (DIXI) - utiliza CPF para identificação
Henry Super Fácil: Formato AFD Henry Super Fácil - utiliza PIS/PASEP para identificação
Henry Prisma: Formato AFD Henry Prisma Super Fácil (proprietário) - utiliza PIS/PASEP para identificação
Henry Orion 5: Formato AFD Henry Orion 5 - utiliza matrícula para identificação
```

### 2. Teste com Arquivo DIXI (CPF)

```php
// Criar registro de importação
$import = AfdImport::create([
    'file_name' => 'teste_dixi.txt',
    'file_path' => 'afd/teste_dixi.txt',
    'status' => 'processing',
    'imported_by' => 1,
]);

// Processar
$result = $service->parse(storage_path('app/afd/teste_dixi.txt'), $import);

// Verificar resultado
print_r($result);

// Verificar formato detectado
echo "Formato: " . $import->fresh()->format_type;
```

**Resultado Esperado**:
```
Array (
    [success] => 1
    [imported] => 25
    [skipped] => 2
    [errors] => Array ()
    [format] => DIXI
)
Formato: DIXI
```

### 3. Teste com Arquivo Henry Super Fácil (PIS)

```php
$import = AfdImport::create([
    'file_name' => 'teste_henry_sf.txt',
    'file_path' => 'afd/teste_henry_sf.txt',
    'status' => 'processing',
]);

$result = $service->parse(storage_path('app/afd/teste_henry_sf.txt'), $import);
print_r($result);
```

**Resultado Esperado**: `[format] => Henry Super Fácil`

### 4. Teste com Arquivo Henry Prisma (PIS)

```php
$import = AfdImport::create([
    'file_name' => 'teste_henry_prisma.txt',
    'file_path' => 'afd/teste_henry_prisma.txt',
    'status' => 'processing',
]);

$result = $service->parse(storage_path('app/afd/teste_henry_prisma.txt'), $import);
print_r($result);
```

**Resultado Esperado**: `[format] => Henry Prisma`

### 5. Teste com Arquivo Henry Orion 5 (Matrícula)

```php
$import = AfdImport::create([
    'file_name' => 'teste_orion5.txt',
    'file_path' => 'afd/teste_orion5.txt',
    'status' => 'processing',
]);

$result = $service->parse(storage_path('app/afd/teste_orion5.txt'), $import);
print_r($result);
```

**Resultado Esperado**: `[format] => Henry Orion 5`

### 6. Teste com Format Hint Manual

```php
// Forçar uso de um parser específico
$import = AfdImport::create([
    'file_name' => 'arquivo_ambiguo.txt',
    'file_path' => 'afd/arquivo_ambiguo.txt',
    'status' => 'processing',
    'format_hint' => 'henry-prisma',
]);

$result = $service->parse(
    storage_path('app/afd/arquivo_ambiguo.txt'), 
    $import, 
    'henry-prisma'  // Force Henry Prisma
);
```

### 7. Teste de Busca de Colaboradores

```php
use App\Models\Employee;

// Criar colaboradores de teste
$empCpf = Employee::create([
    'establishment_id' => 1,
    'full_name' => 'Teste CPF',
    'cpf' => '12345678901',
    'admission_date' => now(),
]);

$empPis = Employee::create([
    'establishment_id' => 1,
    'full_name' => 'Teste PIS',
    'cpf' => '98765432109',
    'pis_pasep' => '12345678901',
    'admission_date' => now(),
]);

$empMatricula = Employee::create([
    'establishment_id' => 1,
    'full_name' => 'Teste Matrícula',
    'cpf' => '11122233344',
    'matricula' => 'MAT001',
    'admission_date' => now(),
]);

// Verificar que foram criados
echo "Criados: {$empCpf->id}, {$empPis->id}, {$empMatricula->id}\n";
```

### 8. Teste de Erros Comuns

```php
// Arquivo inexistente
try {
    $result = $service->parse('/caminho/invalido.txt', $import);
} catch (\Exception $e) {
    echo "Erro esperado: " . $e->getMessage();
}

// Formato não suportado
try {
    $import = AfdImport::create(['file_name' => 'formato_invalido.txt']);
    $result = $service->parse(storage_path('app/formato_invalido.txt'), $import);
} catch (\Exception $e) {
    echo "Erro: " . $e->getMessage();
    // "Nenhum parser compatível encontrado..."
}
```

## 📊 Validação de Resultados

### Verificar Registros Importados

```php
use App\Models\TimeRecord;

// Últimos 10 registros importados
$records = TimeRecord::where('imported_from_afd', true)
    ->latest()
    ->limit(10)
    ->with('employee')
    ->get();

foreach ($records as $record) {
    echo "{$record->employee->full_name} - {$record->recorded_at} ({$record->record_type})\n";
}
```

### Verificar Importações por Formato

```php
use App\Models\AfdImport;

$imports = AfdImport::whereNotNull('format_type')
    ->selectRaw('format_type, COUNT(*) as total')
    ->groupBy('format_type')
    ->get();

foreach ($imports as $import) {
    echo "{$import->format_type}: {$import->total} importações\n";
}
```

### Verificar Erros

```php
$failedImports = AfdImport::where('status', 'failed')->get();

foreach ($failedImports as $import) {
    echo "Arquivo: {$import->file_name}\n";
    echo "Erro: {$import->error_message}\n";
    echo "---\n";
}
```

## 🔍 Logs para Debug

Os logs são salvos em `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log | grep "AFD"
```

**Exemplo de log esperado**:
```
[2025-10-30] local.INFO: AfdParserService: Iniciando processamento do arquivo /path/to/file.txt
[2025-10-30] local.INFO: AFD Parser Factory: Detectado formato Henry Prisma
[2025-10-30] local.INFO: AFD Parser (Henry Prisma): Processando arquivo /path/to/file.txt
```

## ✅ Checklist de Validação

- [ ] Todos os 4 formatos são detectados corretamente
- [ ] Colaboradores são encontrados por CPF (DIXI)
- [ ] Colaboradores são encontrados por PIS (Henry SF e Prisma)
- [ ] Colaboradores são encontrados por Matrícula (Orion 5)
- [ ] Registros duplicados são pulados
- [ ] Formato detectado é salvo em `afd_imports.format_type`
- [ ] Erros são logados corretamente
- [ ] Transações DB funcionam (rollback em caso de erro)

## 🚨 Problemas Comuns

### Problema: "Colaborador não encontrado"
**Solução**: Verifique se:
- O colaborador existe no banco
- O campo correto está preenchido (CPF/PIS/Matrícula)
- Os dados não têm espaços ou caracteres especiais extras

### Problema: "Nenhum parser compatível"
**Solução**:
- Verifique o formato do arquivo
- Use format_hint para forçar um parser específico
- Consulte os logs para detalhes

### Problema: "Todos os registros são pulados"
**Solução**:
- Verifique se as datas no arquivo são válidas
- Confirme que não são duplicatas
- Verifique o array de erros retornado

---

**Última atualização**: 30/10/2025
