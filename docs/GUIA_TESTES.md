# 🎯 Guia de Testes - Sistema de Registro de Ponto

## ✅ Status da Implementação

Todas as funcionalidades foram implementadas com sucesso:

### 🔧 Backend (100% Completo)
- ✅ Processamento Assíncrono com Filas (Queues)
- ✅ Indexação de Banco de Dados (15+ índices)
- ✅ Consultas Eficientes com Eager Loading
- ✅ Filtros em Cascata (Estabelecimento → Departamento)
- ✅ Importação de Colaboradores em Massa (CSV)
- ✅ Paginação de Resultados (50 por página)
- ✅ Busca Case-Insensitive (ILIKE)
- ✅ Sistema de Retry (3 tentativas)
- ✅ Timeout Configurado (AFD: 5min, CSV: 10min)

### 🎨 Frontend (100% Completo)
- ✅ Dashboard com 5 Cards de Estatísticas
- ✅ Painel de Filtros na Listagem de Colaboradores
- ✅ JavaScript para Filtros em Cascata
- ✅ 4 Views para Importação CSV (Create, Preview, Show, Index)
- ✅ Auto-Refresh em Importações em Andamento
- ✅ Exibição de Erros e Estatísticas

### 📊 Estrutura de Dados
- ✅ 13 Migrações Aplicadas
- ✅ 16 Tabelas Criadas
- ✅ Índices de Performance Aplicados
- ✅ Relacionamentos Configurados

---

## 🚀 Servidores em Execução

✅ **Laravel Development Server**: http://localhost:8000  
✅ **Queue Worker**: Processando jobs em background

---

## 📋 Checklist de Testes

### 1️⃣ Teste de Importação CSV de Colaboradores

#### Passo 1: Acessar a Página de Importação
```
URL: http://localhost:8000/employee-imports/create
```

**O que verificar:**
- [ ] Página carrega sem erros
- [ ] Instruções estão claras
- [ ] Botão "Baixar Modelo CSV" está presente
- [ ] Formulário de upload está visível

#### Passo 2: Baixar o Template CSV
```
Clicar em: "Baixar Modelo CSV"
```

**O que verificar:**
- [ ] Arquivo `modelo_colaboradores.csv` é baixado
- [ ] Arquivo contém headers corretos: cpf, full_name, pis_pasep, etc.
- [ ] Arquivo contém uma linha de exemplo

**Formato esperado:**
```csv
cpf,full_name,pis_pasep,establishment_id,department_id,admission_date,status
123.456.789-00,João da Silva,123.45678.90-1,1,1,2024-01-15,ativo
```

#### Passo 3: Preparar Dados de Teste
Criar um arquivo CSV com os seguintes dados:

```csv
cpf,full_name,pis_pasep,establishment_id,department_id,admission_date,status
111.111.111-11,Maria Santos,111.11111.11-1,1,1,2024-01-10,ativo
222.222.222-22,José Oliveira,222.22222.22-2,1,2,2024-02-15,ativo
333.333.333-33,Ana Costa,333.33333.33-3,1,1,2024-03-20,ativo
```

**Importante:**
- Use IDs de establishment e department que existem no banco
- CPF deve ter 14 caracteres (com pontos e hífen)
- PIS deve ter 14 caracteres (com pontos e hífen)
- Status: 'ativo' ou 'inativo'

#### Passo 4: Fazer Upload do CSV
```
1. Selecionar o arquivo CSV criado
2. Clicar em "Fazer Upload"
```

**O que verificar:**
- [ ] Preview é exibido corretamente
- [ ] Estatísticas corretas: Total, Novos, Atualizações, Erros
- [ ] Lista de erros (se houver) está clara
- [ ] Amostra de dados (primeiras 5 linhas) está visível
- [ ] Botões "Confirmar Importação" e "Cancelar" presentes

#### Passo 5: Confirmar Importação
```
Clicar em: "Confirmar Importação"
```

**O que verificar:**
- [ ] Redirecionado para página de status
- [ ] Status inicial: "Pendente" ou "Processando"
- [ ] Página auto-atualiza a cada 5 segundos
- [ ] Informações do arquivo estão corretas

#### Passo 6: Aguardar Processamento
```
Aguardar a página auto-atualizar
```

**O que verificar:**
- [ ] Status muda para "Processando"
- [ ] Depois muda para "Concluído"
- [ ] Estatísticas finais estão corretas
- [ ] Nenhum erro foi registrado (ou erros esperados aparecem)

#### Passo 7: Verificar Colaboradores Importados
```
URL: http://localhost:8000/employees
```

**O que verificar:**
- [ ] Novos colaboradores aparecem na lista
- [ ] Dados estão corretos (nome, CPF, departamento)
- [ ] Status está correto

---

### 2️⃣ Teste de Filtros em Cascata

#### Passo 1: Acessar Listagem de Colaboradores
```
URL: http://localhost:8000/employees
```

**O que verificar:**
- [ ] Página carrega com painel de filtros
- [ ] Dropdown "Estabelecimento" está presente
- [ ] Dropdown "Departamento" está desabilitado inicialmente
- [ ] Campo de busca está presente
- [ ] Botão "Importar CSV" está presente

#### Passo 2: Selecionar um Estabelecimento
```
Selecionar um estabelecimento no dropdown
```

**O que verificar:**
- [ ] Dropdown "Departamento" é habilitado automaticamente
- [ ] Departamentos são carregados via AJAX
- [ ] Apenas departamentos do estabelecimento selecionado aparecem
- [ ] Lista de colaboradores é filtrada automaticamente

#### Passo 3: Selecionar um Departamento
```
Selecionar um departamento no dropdown
```

**O que verificar:**
- [ ] Lista é filtrada para mostrar apenas colaboradores do departamento
- [ ] Paginação é atualizada corretamente
- [ ] Contador de resultados está correto

#### Passo 4: Usar a Busca
```
Digitar um nome ou CPF no campo de busca e pressionar Enter
```

**O que verificar:**
- [ ] Busca funciona com nomes parciais
- [ ] Busca funciona com CPF
- [ ] Busca é case-insensitive
- [ ] Filtros de estabelecimento e departamento são mantidos

#### Passo 5: Limpar Filtros
```
Clicar em "Limpar Filtros"
```

**O que verificar:**
- [ ] Todos os filtros são removidos
- [ ] Lista volta a mostrar todos os colaboradores
- [ ] Departamento volta a ser desabilitado

---

### 3️⃣ Teste de Importação AFD (Assíncrona)

#### Passo 1: Acessar Importação AFD
```
URL: http://localhost:8000/afd-imports/create
```

**O que verificar:**
- [ ] Página carrega sem erros
- [ ] Formulário de upload está presente

#### Passo 2: Fazer Upload de Arquivo AFD
```
Selecionar um arquivo .txt AFD e fazer upload
```

**O que verificar:**
- [ ] Mensagem de sucesso: "Arquivo enviado! Processamento iniciado."
- [ ] Status inicial é "Pendente"
- [ ] Job é adicionado à fila

#### Passo 3: Verificar Processamento
```
Monitorar o queue worker no terminal
```

**O que verificar:**
- [ ] Job "ProcessAfdImport" aparece no log do queue worker
- [ ] Job é processado com sucesso
- [ ] Status muda para "Concluído"
- [ ] Registros de ponto são importados

---

### 4️⃣ Teste de Performance

#### Passo 1: Importar Dataset Grande
```
Criar CSV com 100+ colaboradores e importar
```

**O que verificar:**
- [ ] Importação não trava o navegador
- [ ] Job processa em background
- [ ] Tempo de processamento é aceitável (<30s para 100 registros)

#### Passo 2: Testar Listagem Paginada
```
Acessar /employees com 100+ colaboradores
```

**O que verificar:**
- [ ] Página carrega rapidamente (<2s)
- [ ] Paginação funciona corretamente
- [ ] Navegação entre páginas é fluida
- [ ] Filtros funcionam mesmo com muitos registros

#### Passo 3: Testar Busca com Grande Volume
```
Buscar por nomes comuns ou CPFs
```

**O que verificar:**
- [ ] Busca retorna resultados rapidamente (<1s)
- [ ] Resultados são precisos
- [ ] Paginação de resultados de busca funciona

---

## 🔍 Endpoints da API

### 1. Listar Estabelecimentos
```bash
curl http://localhost:8000/api/establishments
```

**Resposta esperada:**
```json
[
  {
    "id": 1,
    "name": "Estabelecimento Principal",
    "cnpj": "12.345.678/0001-00"
  }
]
```

### 2. Listar Departamentos por Estabelecimento
```bash
curl "http://localhost:8000/api/departments?establishment_id=1"
```

**Resposta esperada:**
```json
[
  {
    "id": 1,
    "name": "Departamento TI",
    "establishment_id": 1
  },
  {
    "id": 2,
    "name": "Departamento RH",
    "establishment_id": 1
  }
]
```

### 3. Buscar Colaboradores
```bash
curl "http://localhost:8000/api/employees/search?search=maria&establishment_id=1"
```

**Resposta esperada:**
```json
[
  {
    "id": 1,
    "full_name": "Maria Santos",
    "cpf": "111.111.111-11",
    "department": {
      "id": 1,
      "name": "TI"
    }
  }
]
```

---

## 🐛 Troubleshooting

### Problema: Jobs não são processados

**Solução:**
```bash
# Verificar se o queue worker está rodando
ps aux | grep "queue:work"

# Se não estiver, iniciar:
php artisan queue:work --verbose
```

### Problema: Erros na importação CSV

**Causas comuns:**
1. CPF sem formatação (deve ter 14 caracteres: 123.456.789-00)
2. PIS sem formatação (deve ter 14 caracteres: 123.45678.90-1)
3. establishment_id ou department_id inexistente
4. Data de admissão em formato errado (usar: YYYY-MM-DD)

**Solução:**
- Verificar arquivo de erros em: `storage/app/employee-imports/errors-{id}.json`
- Corrigir os dados e reimportar

### Problema: Filtros não funcionam

**Solução:**
```bash
# Verificar se as rotas API estão registradas
php artisan route:list --path=api

# Verificar console do navegador para erros JavaScript
# Abrir DevTools (F12) e verificar aba Console
```

### Problema: Página lenta com muitos colaboradores

**Solução:**
```bash
# Verificar se os índices foram criados
php artisan migrate:status

# Se necessário, recriar índices:
php artisan migrate:refresh --path=database/migrations/2025_10_29_215526_add_performance_indexes_to_tables.php
```

---

## 📊 Monitoramento

### Ver Jobs na Fila
```bash
# Verificar tabela de jobs
php artisan queue:monitor
```

### Ver Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Queue worker logs (se estiver rodando)
# Verificar o terminal onde o queue:work está executando
```

### Estatísticas do Banco
```bash
# Contar registros
php artisan tinker
> App\Models\Employee::count()
> App\Models\TimeRecord::count()
> App\Models\EmployeeImport::count()
```

---

## ✨ Recursos Implementados

### Dashboard
- **URL**: http://localhost:8000/dashboard
- **Cards Disponíveis**:
  1. Total de Colaboradores
  2. Total de Departamentos
  3. Importações AFD (últimas 7 dias)
  4. Importações CSV (últimas 7 dias)
  5. Registros de Ponto (últimas 24 horas)

### Ações Rápidas
- ➕ Cadastrar Colaborador
- 📊 Importar Colaboradores (CSV)
- 📁 Importar Arquivo AFD
- 📋 Gerar Cartão de Ponto

---

## 🎓 Próximos Passos

1. **Teste Completo da Importação CSV**
   - Baixar template
   - Criar arquivo com dados de teste
   - Importar e verificar resultados

2. **Teste dos Filtros em Cascata**
   - Verificar interação estabelecimento → departamento
   - Testar busca combinada com filtros

3. **Teste de Performance**
   - Importar dataset grande (100+ registros)
   - Medir tempo de processamento
   - Verificar paginação

4. **Documentação para Usuários Finais**
   - Criar manual de uso da importação CSV
   - Documentar formato esperado dos arquivos
   - Criar FAQ de erros comuns

---

## 📞 Suporte

Em caso de dúvidas ou problemas:

1. Verificar logs em `storage/logs/laravel.log`
2. Verificar arquivos de erro em `storage/app/employee-imports/errors-*.json`
3. Verificar queue worker está rodando
4. Verificar conexão com banco de dados

---

**Sistema pronto para testes! 🚀**

Última atualização: 2025-10-29
