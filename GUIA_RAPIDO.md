# 🚀 GUIA RÁPIDO - Sistema de Ponto

## ✅ Checklist de Inicialização

```bash
# 1. Iniciar servidor Laravel
php artisan serve

# 2. Iniciar queue worker (em outro terminal)
php artisan queue:work

# Pronto! Acesse: http://localhost:8000
```

---

## 📋 Funcionalidades Principais

### 1. 👥 Gerenciar Colaboradores

**URL:** `http://localhost:8000/employees`

**Recursos:**
- ✅ Listar todos os colaboradores (com paginação)
- ✅ Filtrar por estabelecimento
- ✅ Filtrar por departamento
- ✅ Buscar por nome ou CPF
- ✅ Criar/Editar/Visualizar colaborador
- ✅ Ver registros de ponto

**Como usar:**
1. Acesse a listagem
2. Use os filtros no topo da página
3. Digite na busca para encontrar rapidamente
4. Clique em "Novo Colaborador" para adicionar

---

### 2. 📤 Importar Colaboradores (CSV)

**URL:** `http://localhost:8000/employee-imports`

**Fluxo:**

```
┌─────────────────────────────────────┐
│ 1. Baixar Modelo CSV                │
│    /modelo-importacao-colaboradores.csv
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 2. Preencher dados no Excel/Sheets │
│    - Nome completo                  │
│    - CPF (11 dígitos)              │
│    - PIS/PASEP (11 dígitos)        │
│    - Email (opcional)              │
│    - Estabelecimento               │
│    - Departamento                  │
│    - Cargo                         │
│    - Data admissão (YYYY-MM-DD)    │
│    - Salário                       │
│    - Status (ativo/inativo)        │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 3. Upload do arquivo                │
│    - Sistema valida automaticamente │
│    - Mostra erros se houver         │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 4. Confirmar importação             │
│    - Processa em background         │
│    - Acompanhe o status             │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 5. Verificar resultado              │
│    - Criados: X                     │
│    - Atualizados: Y                 │
│    - Erros: Z                       │
└─────────────────────────────────────┘
```

**⚠️ Regras Importantes:**
- CPF deve ter exatamente 11 dígitos
- Se CPF já existe, colaborador é **atualizado**
- Estabelecimento e departamento são criados automaticamente se não existirem
- Data deve estar no formato **YYYY-MM-DD** (ex: 2024-01-15)

---

### 3. ⏰ Importar Arquivo AFD

**URL:** `http://localhost:8000/afd-imports`

**O que é AFD?**
Arquivo Fiscal Digital - formato oficial para registro de ponto eletrônico (Portaria 671/2021 MTP)

**Como usar:**
1. Acesse "Importações AFD"
2. Clique em "Nova Importação"
3. Faça upload do arquivo `.txt` do relógio de ponto
4. Sistema processa em background
5. Página atualiza automaticamente com o status

**Status:**
- 🟡 **Pendente**: Na fila para processar
- 🔵 **Processando**: Worker está processando
- 🟢 **Concluído**: Todos os registros importados
- 🔴 **Falhou**: Erro no processamento

---

### 4. 📊 Gerar Folha de Ponto

**URL:** `http://localhost:8000/employees/{id}` → botão "Gerar Folha de Ponto"

**Como usar:**
1. Acesse o perfil de um colaborador
2. Clique em "Gerar Folha de Ponto"
3. Selecione período (data inicial e final)
4. Sistema gera PDF com:
   - Todas as batidas do período
   - Horas trabalhadas
   - Horas extras
   - Faltas/Atrasos
   - Totais

---

## 🎯 Casos de Uso Comuns

### Caso 1: Cadastrar Novo Estabelecimento + Colaboradores

```
1. Acesse /employee-imports
2. Baixe o modelo CSV
3. Preencha:
   - Nome: João Silva
   - CPF: 12345678901
   - Estabelecimento: Filial Norte
   - Departamento: Vendas
   - ...
4. Upload
5. Sistema cria automaticamente:
   ✅ Estabelecimento "Filial Norte"
   ✅ Departamento "Vendas"
   ✅ Colaborador "João Silva"
```

### Caso 2: Atualizar Dados de Colaborador Existente

```
1. Prepare CSV com CPF já cadastrado
2. Altere os dados que deseja (nome, cargo, etc)
3. Upload
4. Sistema atualiza o colaborador existente
```

### Caso 3: Buscar Colaborador Rapidamente

```
1. Acesse /employees
2. Digite no campo de busca:
   - Nome parcial: "joão"
   - CPF: "123.456"
3. Resultados aparecem em tempo real
```

### Caso 4: Ver Registros de Ponto de um Colaborador

```
1. Acesse /employees
2. Clique no nome do colaborador
3. Role até "Registros de Ponto Recentes"
4. Veja últimos 10 registros
5. Clique em "Gerar Folha de Ponto" para relatório completo
```

---

## 🐛 Troubleshooting

### Problema: "Importação está pendente há muito tempo"

**Solução:**
```bash
# Verificar se o worker está rodando
ps aux | grep "queue:work"

# Se não estiver, iniciar:
php artisan queue:work
```

### Problema: "Erro ao importar CSV"

**Checklist:**
- ✅ CPF tem 11 dígitos? (sem pontos ou traços)
- ✅ PIS tem 11 dígitos?
- ✅ Data no formato YYYY-MM-DD?
- ✅ Status é "ativo" ou "inativo"?
- ✅ Arquivo é .csv?

### Problema: "Página lenta ao listar colaboradores"

**Possíveis causas:**
- Worker não está rodando
- Muitas queries N+1
- Índices não criados

**Solução:**
```bash
# Verificar índices
php artisan tinker
>>> DB::select("SELECT * FROM pg_indexes WHERE tablename = 'employees'");

# Recriar índices se necessário
php artisan migrate:fresh --seed
```

### Problema: "Worker para de processar"

**Solução:**
```bash
# Ver jobs falhados
php artisan queue:failed

# Reiniciar worker
php artisan queue:restart
php artisan queue:work
```

---

## 📊 Monitoramento

### Ver Filas em Tempo Real

```bash
# Ver jobs na fila
php artisan tinker
>>> DB::table('jobs')->count();

# Ver jobs falhados
php artisan queue:failed
```

### Logs

```bash
# Ver últimas 50 linhas
tail -n 50 storage/logs/laravel.log

# Acompanhar em tempo real
tail -f storage/logs/laravel.log
```

### Performance

```bash
# Ver queries lentas
php artisan tinker
>>> DB::enableQueryLog();
>>> // Fazer operação
>>> DB::getQueryLog();
```

---

## ⚡ Dicas de Performance

### 1. Sempre iniciar o Queue Worker

```bash
# Desenvolvimento
php artisan queue:work

# Produção (com supervisor)
sudo supervisorctl start registro-ponto-worker:*
```

### 2. Limpar caches periodicamente

```bash
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 3. Importações grandes?

- Use CSV ao invés de formulários
- Importe em lotes de 500-1000 registros
- Aguarde processamento completar antes de nova importação

### 4. Banco de dados lento?

```bash
# Verificar índices
EXPLAIN ANALYZE SELECT * FROM employees WHERE cpf = '12345678901';

# Recriar estatísticas
VACUUM ANALYZE;
```

---

## 🔒 Segurança

### Dados Sensíveis

- ✅ CPF é indexado mas não exposto em logs
- ✅ Senhas nunca são logadas
- ✅ Validação em todos os inputs
- ✅ CSRF protection habilitado

### Backup

```bash
# Backup diário recomendado
pg_dump -U postgres registro_ponto > backup-$(date +%Y%m%d).sql
```

---

## 📞 Comandos Úteis

```bash
# Limpar jobs falhados antigos
php artisan queue:prune-failed --hours=48

# Reprocessar job específico
php artisan queue:retry [job-id]

# Reprocessar todos jobs falhados
php artisan queue:retry all

# Ver status do sistema
php artisan about

# Verificar configuração de filas
php artisan queue:monitor

# Criar backup
php artisan backup:run

# Otimizar para produção
php artisan optimize
```

---

## 🎓 Próximos Passos

1. ✅ Importar colaboradores via CSV
2. ✅ Importar arquivo AFD
3. ✅ Configurar escalas de trabalho
4. ✅ Gerar folhas de ponto
5. ✅ Exportar relatórios
6. ⭐ Configurar notificações
7. ⭐ Integrar com sistema de pagamento

---

**💡 Lembre-se:** O sistema processa tudo em background. Se algo parecer lento, verifique se o queue worker está rodando!

**🚀 Sistema pronto para 600+ colaboradores!**
