# 📚 Arquitetura Multi-Parser AFD

## 🎯 Visão Geral

O sistema de importação de arquivos AFD foi completamente redesenhado para suportar **múltiplos formatos de relógios de ponto** de forma extensível e manutenível.

## 🏗️ Arquitetura Implementada

### Padrões de Projeto Utilizados

1. **Strategy Pattern**: Cada parser é uma estratégia diferente de processamento
2. **Factory Pattern**: Factory cria o parser apropriado baseado no arquivo
3. **Template Method**: BaseAfdParser define o fluxo comum, subclasses implementam detalhes

### Estrutura de Classes

```
AfdParserInterface (Interface)
    ↓
BaseAfdParser (Classe Abstrata)
    ↓
    ├── DixiParser
    ├── HenrySuperFacilParser
    ├── HenryPrismaParser
    └── HenryOrion5Parser

AfdParserFactory (Factory)
    └── Cria instâncias dos parsers acima
```

## 📋 Formatos Suportados

### 1. **DIXI** (DixiParser)
- **Formato**: Portaria 1510/2009 padrão
- **Identificação**: CPF
- **Estrutura**: NSR (9) + Tipo (1) + Data ISO (24) + CPF (12)
- **Exemplo de linha tipo 3**:
  ```
  0000000032025-10-28T08:30:00.000000912345678901
  ```

### 2. **Henry Super Fácil** (HenrySuperFacilParser)
- **Formato**: Data/hora compacta
- **Identificação**: PIS/PASEP
- **Estrutura**: NSR (9) + Tipo (1) + Data compacta (12: ddmmyyyyHHMM) + PIS (12)
- **Exemplo de linha tipo 3**:
  ```
  0000000333040620140657020050673887
  ```
  - NSR: `000000033`
  - Tipo: `3`
  - Data: `040620140657` = 04/06/2014 06:57
  - PIS: `020050673887`

### 3. **Henry Prisma** (HenryPrismaParser)
- **Formato**: Proprietário com checksum hexadecimal
- **Identificação**: PIS/PASEP
- **Estrutura**: Data (8: ddmmyyyy) + Hora (4: HHMM) + PIS (11) + Checksum (4 hex)
- **Exemplo**:
  ```
  3009202507000190441830206FAE
  ```
  - Data: `30092025` = 30/09/2025
  - Hora: `0700` = 07:00
  - PIS: `01904418302`
  - Checksum: `06FAE`

### 4. **Henry Orion 5** (HenryOrion5Parser)
- **Formato**: Simplificado com matrícula
- **Identificação**: Matrícula do funcionário
- **Estrutura**: MATRICULA (variável) + Data (8: ddmmyyyy) + Hora (4: HHMM)
- **Exemplo**:
  ```
  001201022025093000
  ```
  - Matrícula: `0012`
  - Data: `01022025` = 01/02/2025
  - Hora: `0930` = 09:30

## 🔍 Detecção Automática de Formato

A Factory implementa um sistema inteligente de detecção:

1. **Por Hint Manual** (opcional): Usuário pode especificar o formato
2. **Detecção Automática**: Se não houver hint, tenta cada parser em ordem
3. **Ordem de Tentativa**: Do mais específico ao mais genérico

### Lógica de Detecção

Cada parser implementa `canParse(string $filePath): bool`:

- **HenryPrismaParser**: Verifica checksum hexadecimal + padrão de data
- **HenryOrion5Parser**: Verifica linhas curtas + padrão alfanumérico
- **HenrySuperFacilParser**: Verifica data compacta de 12 dígitos
- **DixiParser**: Verifica data ISO + estrutura padrão 1510

## 🔄 Fluxo de Processamento

```
1. AfdParserService.parse()
   ↓
2. AfdParserFactory.createParser()
   ↓
3. [Detecção automática ou por hint]
   ↓
4. Parser específico.parse()
   ↓
5. processFile() → Parse de cada linha
   ↓
6. findEmployee() → Busca por PIS/Matrícula/CPF
   ↓
7. createTimeRecord() → Registra o ponto
```

## 🗃️ Banco de Dados

### Tabela `employees`

```sql
- cpf (indexed) - para DIXI
- pis_pasep (indexed) - para Henry Super Fácil e Prisma
- matricula (indexed) - para Henry Orion 5
```

### Tabela `afd_imports`

```sql
- format_type - Nome do formato detectado
- format_hint - Hint fornecido pelo usuário (opcional)
```

## 💡 Busca Unificada de Colaboradores

A classe `BaseAfdParser` implementa o método `findEmployee()` que busca em ordem:

```php
1. Por PIS/PASEP (se fornecido)
2. Por Matrícula (se fornecido)
3. Por CPF (se fornecido)
```

Cada parser específico chama este método com os parâmetros corretos:

- **DixiParser**: `findEmployee(null, null, $cpf)`
- **HenrySuperFacilParser**: `findEmployee($pis, null, null)`
- **HenryPrismaParser**: `findEmployee($pis, null, null)`
- **HenryOrion5Parser**: `findEmployee(null, $matricula, null)`

## 🚀 Como Usar

### Importação Automática

```php
$parserService = new AfdParserService();
$result = $parserService->parse($filePath, $afdImport);
// Sistema detecta automaticamente o formato
```

### Importação com Hint

```php
$result = $parserService->parse($filePath, $afdImport, 'henry-prisma');
// Força uso do parser Henry Prisma
```

### Listar Formatos Suportados

```php
$formats = $parserService->getSupportedFormats();
// Retorna array com todos os formatos disponíveis
```

## ➕ Adicionar Novo Parser

Para adicionar suporte a um novo modelo de relógio:

1. **Criar nova classe** em `app/Services/AfdParsers/`
   ```php
   class NovoModeloParser extends BaseAfdParser
   {
       public function getFormatName(): string { return 'Novo Modelo'; }
       public function canParse(string $filePath): bool { /* lógica */ }
       protected function processFile(string $filePath): void { /* lógica */ }
   }
   ```

2. **Registrar na Factory**
   ```php
   AfdParserFactory::registerParser(NovoModeloParser::class);
   ```

3. **Adicionar hint ao mapping** (opcional)
   ```php
   'novo-modelo' => NovoModeloParser::class,
   ```

## 🧪 Testando

### Teste Manual

```bash
# Via Artisan Tinker
php artisan tinker

$service = new App\Services\AfdParserService();
$import = App\Models\AfdImport::first();
$result = $service->parse('caminho/arquivo.txt', $import);
print_r($result);
```

### Verificar Formato Detectado

```php
$import = AfdImport::find(1);
echo $import->format_type; // Ex: "Henry Prisma"
```

## 📊 Resultado do Processamento

```php
[
    'success' => true,
    'imported' => 150,           // Registros importados
    'skipped' => 5,              // Registros pulados (duplicados ou inválidos)
    'errors' => [],              // Lista de erros
    'format' => 'Henry Prisma'   // Formato detectado
]
```

## 🔐 Vantagens da Arquitetura

✅ **Extensível**: Adicionar novo parser é simples e não afeta os existentes
✅ **Manutenível**: Cada parser é independente e focado em um formato
✅ **Testável**: Cada componente pode ser testado isoladamente
✅ **Robusto**: Detecção automática com fallback
✅ **Documentado**: Código autodocumentado e com comentários
✅ **À prova de futuro**: Fácil adicionar novos modelos

## 📝 Observações Importantes

1. **Ordem dos parsers importa**: Mais específicos primeiro
2. **Índices no BD**: Garanta que cpf, pis_pasep e matricula estejam indexados
3. **Normalização**: Todos os campos são normalizados antes da busca
4. **Duplicatas**: Sistema previne importação de registros duplicados
5. **Transações**: Todo processamento é feito em transação DB

## 🐛 Troubleshooting

### "Nenhum parser compatível encontrado"
- Verifique se o arquivo está no formato esperado
- Tente especificar o formato manualmente com hint
- Verifique logs em `storage/logs/laravel.log`

### "Colaborador não encontrado"
- Verifique se o colaborador está cadastrado
- Confirme se o campo correto (CPF/PIS/Matrícula) está preenchido
- Verifique se os dados estão formatados corretamente

### Registros pulados
- Verifique o array `errors` no resultado
- Consulte o log para detalhes dos erros
- Comum: duplicatas, datas inválidas, colaborador não encontrado

---

**Versão**: 1.0  
**Data**: 30/10/2025  
**Autor**: Sistema de Registro de Ponto
