# 📋 SISTEMA DE IMPORTAÇÃO DE VÍNCULOS E JORNADAS

## 🎯 Objetivo

Sistema completo para importar vínculos de colaboradores e associar jornadas de trabalho a partir de arquivo CSV legado do sistema anterior.

## 📊 Arquitetura

### Conceitos Principais

1. **Pessoa (PIS/PASEP)**: Identificador único da pessoa física
2. **Vínculo (Matrícula)**: Relação de trabalho da pessoa com a organização
3. **Jornada (Template)**: Modelo de horário de trabalho associado ao vínculo

### Fluxo de Dados

```
CSV Legado → Job (Fila) → Banco de Dados
                          ├── Pessoas (people)
                          ├── Vínculos (employee_registrations)
                          └── Atribuições (employee_work_shift_assignments)
```

## 📁 Estrutura do CSV

### Formato Esperado

```csv
NOME,Nº PIS/PASEP,Nº IDENTIFICADOR,HORÁRIO,HORÁRIO_LIMPO
João Silva,12345678901,M001,"7 - SAÚDE -07:30-11:30-13:00-17:00","7h/dia"
Maria Santos,98765432100,M002,"219 - SEC - 15-20 E 21-00","Secretaria"
```

### Colunas

| Coluna | Obrigatória | Descrição | Exemplo |
|--------|-------------|-----------|---------|
| **NOME** | ✅ Sim | Nome completo da pessoa | "João da Silva" |
| **Nº PIS/PASEP** | ✅ Sim | Identificador único (11 dígitos) | "12345678901" |
| **Nº IDENTIFICADOR** | ✅ Sim | Matrícula do vínculo | "M001" |
| **HORÁRIO** | ✅ Sim | Campo com ID da jornada | "7 - SAÚDE..." |
| **HORÁRIO_LIMPO** | ❌ Não | Descrição textual | "7h/dia" |

## 🔧 Lógica de Importação

### Job: `ImportVinculosJob`

#### Processamento de Cada Linha

```php
1. BUSCAR/CRIAR PESSOA
   - Busca pelo PIS/PASEP
   - Se não existe: cria nova pessoa
   - Se existe: atualiza o nome

2. BUSCAR/CRIAR VÍNCULO
   - Busca pela Matrícula
   - Se não existe: cria novo vínculo
   - Se existe: atualiza person_id

3. EXTRAIR ID DA JORNADA
   - Parser: "7 - SAÚDE..." → 7
   - Regex: /^(\d+)\s*-/

4. ASSOCIAR JORNADA
   - Cria employee_work_shift_assignment
   - Apenas se o template existir
   - Não duplica se já existe
```

### Parser de ID da Jornada

O parser extrai o número inicial do campo HORÁRIO:

**Entrada**: `"7 - SAÚDE -07:30-11:30-13:00-17:00"`  
**Saída**: `7`

**Entrada**: `"219 - SEC - 15-20 E 21-00"`  
**Saída**: `219`

Implementação:
```php
protected function parseWorkShiftId(string $horario): ?int
{
    if (preg_match('/^(\d+)\s*-/', $horario, $matches)) {
        return (int) $matches[1];
    }
    return null;
}
```

## 🎨 Interface de Upload

### Rota: `/vinculo-imports/create`

#### Funcionalidades

- ✅ Upload de arquivo CSV (máx 10MB)
- ✅ Validação automática do formato
- ✅ Preview: quantidade de linhas a processar
- ✅ Processamento em fila (background)
- ✅ Feedback em tempo real

#### Validações Pré-Upload

1. **Formato**: Apenas .csv ou .txt
2. **Header**: Valida colunas obrigatórias
3. **Tamanho**: Máximo 10MB

### Tela de Resultados

#### Rota: `/vinculo-imports/{id}`

**Estatísticas Exibidas:**

- 📊 Total de linhas processadas
- 👤 Pessoas criadas/atualizadas
- 🆔 Vínculos criados/atualizados
- ⏰ Jornadas associadas
- ❌ Erros encontrados
- 📈 Taxa de sucesso (%)

**Ações Disponíveis:**

- 📥 Download do CSV original
- 🔍 Ver erros detalhados (se houver)
- 📄 Download do relatório de erros

## 📊 Estrutura do Banco

### Tabela: `vinculo_imports`

```sql
CREATE TABLE vinculo_imports (
    id BIGINT PRIMARY KEY,
    filename VARCHAR(255),
    csv_path VARCHAR(255),
    user_id BIGINT NULLABLE,
    total_linhas INT DEFAULT 0,
    pessoas_criadas INT DEFAULT 0,
    pessoas_atualizadas INT DEFAULT 0,
    vinculos_criados INT DEFAULT 0,
    vinculos_atualizados INT DEFAULT 0,
    jornadas_associadas INT DEFAULT 0,
    erros INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed'),
    error_message TEXT NULLABLE,
    started_at TIMESTAMP NULLABLE,
    completed_at TIMESTAMP NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Relacionamentos

```
vinculo_imports
    ├── belongsTo: User (quem importou)
    └── storage: JSON com resultados e erros
```

## 📂 Arquivos Criados

### Backend

| Arquivo | Descrição |
|---------|-----------|
| `app/Jobs/ImportVinculosJob.php` | Job de processamento em fila |
| `app/Models/VinculoImport.php` | Model da importação |
| `app/Http/Controllers/VinculoImportController.php` | Controller |
| `database/migrations/..._create_vinculo_imports_table.php` | Migration |

### Frontend

| View | Rota | Descrição |
|------|------|-----------|
| `vinculo-imports/index.blade.php` | `/vinculo-imports` | Histórico |
| `vinculo-imports/upload.blade.php` | `/vinculo-imports/create` | Upload |
| `vinculo-imports/show.blade.php` | `/vinculo-imports/{id}` | Resultados |
| `vinculo-imports/errors.blade.php` | `/vinculo-imports/{id}/errors` | Erros |

### Rotas

```php
Route::prefix('vinculo-imports')->name('vinculo-imports.')->group(function () {
    Route::get('/', 'index')->name('index');                              // Histórico
    Route::get('/create', 'create')->name('create');                      // Upload
    Route::post('/', 'store')->name('store');                             // Processar
    Route::get('/{import}', 'show')->name('show');                        // Resultados
    Route::get('/{import}/errors', 'showErrors')->name('errors');         // Erros
    Route::get('/{import}/download', 'download')->name('download');       // CSV
    Route::get('/{import}/download-errors', 'downloadErrors')->name('download-errors'); // Erros CSV
});
```

## 🚀 Como Usar

### 1. Preparar o Arquivo CSV

```csv
NOME,Nº PIS/PASEP,Nº IDENTIFICADOR,HORÁRIO,HORÁRIO_LIMPO
João Silva,12345678901,M001,"7 - SAÚDE -07:30-11:30...","Jornada Saúde"
```

### 2. Acessar Interface de Upload

```
Navegação: Menu > EQUIPAMENTOS > Importar Vínculos
URL: /vinculo-imports/create
```

### 3. Fazer Upload

- Selecionar arquivo CSV
- Clicar em "Iniciar Importação"
- Sistema valida e inicia processamento

### 4. Acompanhar Progresso

- Redirecionamento automático para tela de resultados
- Auto-refresh enquanto processando
- Estatísticas atualizadas em tempo real

### 5. Verificar Resultados

**Se houver erros:**
- Clicar em "Ver Erros Detalhados"
- Baixar relatório de erros em CSV
- Corrigir linhas com erro
- Re-importar

**Se tudo OK:**
- Verificar estatísticas
- Confirmar pessoas/vínculos criados
- Validar jornadas associadas

## 🔍 Tratamento de Erros

### Tipos de Erro Comuns

| Erro | Causa | Solução |
|------|-------|---------|
| "PIS/PASEP é obrigatório" | Campo vazio | Preencher PIS |
| "Matrícula é obrigatória" | Campo vazio | Preencher matrícula |
| "NOME é obrigatório" | Campo vazio | Preencher nome |
| Jornada não encontrada | ID não existe | Criar template primeiro |

### Tela de Erros

**Funcionalidades:**

- 🔍 Busca em tempo real
- 📊 Contador de erros
- 📋 Detalhes completos de cada linha
- 💾 Download do relatório
- 🎯 Modal com dados completos

## 📈 Estatísticas e Relatórios

### Métricas Calculadas

```php
// Taxa de sucesso
success_rate = ((total - erros) / total) * 100

// Pessoas processadas
pessoas_processadas = pessoas_criadas + pessoas_atualizadas

// Vínculos processados
vinculos_processados = vinculos_criados + vinculos_atualizados
```

### Arquivos de Resultado

**Localização:** `storage/app/vinculo-imports/`

```
results-{id}.json       → Estatísticas gerais
errors-{id}.json        → Detalhes dos erros
{timestamp}_{filename}  → CSV original
```

## 🎯 Casos de Uso

### Caso 1: Primeira Importação (Sistema Vazio)

```
CSV: 1000 linhas
Resultado:
  - 1000 pessoas criadas
  - 1000 vínculos criados
  - 850 jornadas associadas (150 sem template)
```

### Caso 2: Atualização (Sistema com Dados)

```
CSV: 1000 linhas (mesmas pessoas/vínculos)
Resultado:
  - 1000 pessoas atualizadas
  - 1000 vínculos atualizados
  - 850 jornadas associadas
```

### Caso 3: Misto (Novos + Existentes)

```
CSV: 1000 linhas
Resultado:
  - 300 pessoas criadas
  - 700 pessoas atualizadas
  - 500 vínculos criados
  - 500 vínculos atualizados
```

## ⚠️ Avisos Importantes

1. **Processamento em Fila**
   - Não bloqueia a interface
   - Pode levar alguns minutos
   - Auto-refresh na tela de resultados

2. **Vínculos Duplicados**
   - Matrícula é única
   - Sistema atualiza em vez de duplicar

3. **Jornadas Inexistentes**
   - Vínculo criado mesmo sem jornada
   - Log de aviso gerado
   - Pode associar manualmente depois

4. **Tamanho do Arquivo**
   - Máximo: 10MB
   - Aproximadamente: 50.000 linhas
   - Arquivos maiores: dividir em partes

## 🔐 Segurança

- ✅ Autenticação obrigatória
- ✅ Validação de formato
- ✅ Sanitização de dados (PIS)
- ✅ Transações de banco (atomicidade)
- ✅ Logs de erro completos

## 🏁 Conclusão

O sistema de importação de vínculos foi projetado para:

- ✅ Facilitar migração de sistema legado
- ✅ Processar grandes volumes de dados
- ✅ Garantir integridade referencial
- ✅ Fornecer feedback detalhado
- ✅ Permitir correção de erros

**Próximos Passos:**

1. Criar templates de jornada antes de importar
2. Preparar CSV com dados do sistema legado
3. Fazer importação de teste (pequeno volume)
4. Validar resultados
5. Executar importação completa
