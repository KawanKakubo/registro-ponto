# 🛠️ COMANDOS ÚTEIS - Sistema de Ponto

## 🚀 INICIALIZAÇÃO

```bash
# Iniciar servidor Laravel (Terminal 1)
php artisan serve

# Iniciar queue worker (Terminal 2 - OBRIGATÓRIO!)
php artisan queue:work

# Iniciar queue worker com configurações otimizadas
php artisan queue:work --tries=3 --timeout=300 --sleep=3

# Acessar sistema
http://localhost:8000
```

---

## �� MONITORAMENTO

### Ver Status das Filas

```bash
# Ver jobs na fila (database)
php artisan tinker
>>> DB::table('jobs')->count();
>>> DB::table('jobs')->get();

# Ver jobs falhados
php artisan queue:failed

# Ver jobs falhados (detalhado)
php artisan queue:failed-table
```

### Monitorar Filas em Tempo Real

```bash
# Alertar se fila > 100 jobs
php artisan queue:monitor default --max=100

# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Ver últimas 100 linhas
tail -n 100 storage/logs/laravel.log
```

---

## 🔄 GERENCIAR FILAS

### Reprocessar Jobs

```bash
# Reprocessar job específico
php artisan queue:retry [job-id]

# Reprocessar todos os jobs falhados
php artisan queue:retry all

# Limpar jobs falhados antigos (>48h)
php artisan queue:prune-failed --hours=48
```

### Controlar Worker

```bash
# Reiniciar todos os workers gracefully
php artisan queue:restart

# Parar worker (Ctrl+C no terminal)

# Ver se worker está rodando
ps aux | grep "queue:work"

# Matar worker forçadamente (não recomendado)
pkill -f "queue:work"
```

---

## 🗄️ BANCO DE DADOS

### Migrations

```bash
# Rodar todas as migrations
php artisan migrate

# Ver status das migrations
php artisan migrate:status

# Rollback última migration
php artisan migrate:rollback

# Recriar banco do zero (CUIDADO!)
php artisan migrate:fresh

# Recriar e popular com seeders
php artisan migrate:fresh --seed
```

### Verificar Índices

```bash
# Listar índices da tabela employees
php artisan tinker
>>> DB::select("SELECT indexname, indexdef FROM pg_indexes WHERE tablename = 'employees'");

# Listar todos os índices
>>> DB::select("SELECT * FROM pg_indexes WHERE schemaname = 'public'");
```

### Backup e Restore

```bash
# Backup do banco PostgreSQL
pg_dump -U postgres registro_ponto > backup-$(date +%Y%m%d).sql

# Restaurar backup
psql -U postgres registro_ponto < backup-20241029.sql

# Backup com compressão
pg_dump -U postgres registro_ponto | gzip > backup-$(date +%Y%m%d).sql.gz

# Restaurar backup comprimido
gunzip -c backup-20241029.sql.gz | psql -U postgres registro_ponto
```

---

## 🧹 LIMPEZA E MANUTENÇÃO

### Limpar Caches

```bash
# Limpar todos os caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Limpar tudo de uma vez
php artisan optimize:clear
```

### Otimizar para Produção

```bash
# Criar caches otimizados
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Otimizar tudo
php artisan optimize

# Otimizar autoloader do Composer
composer dump-autoload --optimize
```

### Limpar Dados Antigos

```bash
# Limpar jobs falhados (>48h)
php artisan queue:prune-failed --hours=48

# Limpar batches antigos (>24h)
php artisan queue:prune-batches --hours=24

# Limpar logs antigos
find storage/logs -name "*.log" -mtime +30 -delete
```

---

## 🔍 DEBUG E ANÁLISE

### Tinker (Console Interativo)

```bash
# Abrir tinker
php artisan tinker

# Ver estatísticas
>>> DB::table('employees')->count();
>>> DB::table('time_records')->count();
>>> DB::table('employee_imports')->where('status', 'completed')->count();

# Testar queries
>>> $employees = App\Models\Employee::with('establishment')->limit(5)->get();
>>> $employees->each(fn($e) => print($e->full_name . "\n"));

# Ver jobs na fila
>>> DB::table('jobs')->count();

# Ver importações recentes
>>> App\Models\EmployeeImport::latest()->take(5)->get(['id', 'status', 'total_rows']);
```

### Análise de Performance

```bash
# Habilitar query log
php artisan tinker
>>> DB::enableQueryLog();
>>> // executar operações
>>> DB::getQueryLog();

# Analisar query específica (no PostgreSQL)
>>> DB::select("EXPLAIN ANALYZE SELECT * FROM employees WHERE cpf = '12345678901'");

# Ver queries lentas (PostgreSQL)
>>> DB::select("SELECT query, calls, total_time FROM pg_stat_statements ORDER BY total_time DESC LIMIT 10");
```

---

## 📈 ESTATÍSTICAS

### Resumo do Sistema

```bash
php artisan tinker

# Total de colaboradores
>>> App\Models\Employee::count();

# Colaboradores ativos
>>> App\Models\Employee::where('status', 'active')->count();

# Total de estabelecimentos
>>> App\Models\Establishment::count();

# Total de departamentos
>>> App\Models\Department::count();

# Total de registros de ponto
>>> App\Models\TimeRecord::count();

# Registros do mês atual
>>> App\Models\TimeRecord::whereMonth('record_date', now()->month)->count();

# Importações bem-sucedidas
>>> App\Models\EmployeeImport::where('status', 'completed')->count();
```

### Estatísticas Avançadas

```bash
php artisan tinker

# Top 5 estabelecimentos com mais colaboradores
>>> DB::table('employees')
    ->select('establishment_id', DB::raw('count(*) as total'))
    ->groupBy('establishment_id')
    ->orderBy('total', 'desc')
    ->limit(5)
    ->get();

# Colaboradores cadastrados por mês
>>> DB::table('employees')
    ->select(DB::raw('DATE_TRUNC(\'month\', created_at) as month'), DB::raw('count(*) as total'))
    ->groupBy('month')
    ->orderBy('month', 'desc')
    ->get();

# Taxa de sucesso das importações
>>> $total = App\Models\EmployeeImport::count();
>>> $success = App\Models\EmployeeImport::where('status', 'completed')->count();
>>> echo number_format(($success / $total) * 100, 2) . '%';
```

---

## 🧪 TESTES

### Rodar Testes

```bash
# Rodar todos os testes
php artisan test

# Rodar testes específicos
php artisan test --filter EmployeeTest

# Rodar com coverage
php artisan test --coverage
```

### Testar Funcionalidades Manualmente

```bash
# 1. Testar importação de colaboradores
# Criar arquivo test.csv
cat > test.csv << 'CSV'
nome_completo,cpf,pis_pasep,email,telefone,estabelecimento,departamento,cargo,data_admissao,salario,status
Teste Usuario,98765432100,98765432100,teste@test.com,11999999999,Teste Estabelecimento,Teste Depto,Testador,2024-01-01,5000.00,ativo
CSV

# 2. Fazer upload via interface
# http://localhost:8000/employee-imports/create

# 3. Verificar se foi criado
php artisan tinker
>>> App\Models\Employee::where('cpf', '98765432100')->first();
```

---

## 🔧 SUPERVISOR (Produção)

### Configurar Supervisor

```bash
# Criar arquivo de configuração
sudo nano /etc/supervisor/conf.d/registro-ponto.conf

# Conteúdo:
[program:registro-ponto-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /caminho/para/registro-ponto/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/caminho/para/registro-ponto/storage/logs/worker.log
stopwaitsecs=3600
```

### Gerenciar Supervisor

```bash
# Recarregar configuração
sudo supervisorctl reread
sudo supervisorctl update

# Iniciar workers
sudo supervisorctl start registro-ponto-worker:*

# Parar workers
sudo supervisorctl stop registro-ponto-worker:*

# Reiniciar workers
sudo supervisorctl restart registro-ponto-worker:*

# Ver status
sudo supervisorctl status

# Ver logs
sudo supervisorctl tail -f registro-ponto-worker:0 stdout
```

---

## 🚀 DEPLOY

### Preparar para Produção

```bash
# 1. Atualizar código
git pull origin main

# 2. Instalar dependências
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# 3. Rodar migrations
php artisan migrate --force

# 4. Otimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 5. Reiniciar workers
php artisan queue:restart

# 6. Reiniciar servidor web
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Verificar Saúde do Sistema

```bash
# Ver informações do sistema
php artisan about

# Verificar configuração
php artisan config:show

# Testar conexão com banco
php artisan tinker
>>> DB::connection()->getPdo();

# Testar filas
php artisan queue:monitor
```

---

## 📊 LOGS E DEBUGGING

### Ver Logs

```bash
# Últimas 50 linhas
tail -n 50 storage/logs/laravel.log

# Tempo real
tail -f storage/logs/laravel.log

# Buscar erro específico
grep "ERROR" storage/logs/laravel.log

# Ver logs por data
grep "2024-10-29" storage/logs/laravel.log

# Salvar logs filtrados
grep "ERROR" storage/logs/laravel.log > errors.log
```

### Debug Mode

```bash
# Ativar debug (APENAS desenvolvimento!)
# Editar .env
APP_DEBUG=true

# Ver todas as queries
php artisan tinker
>>> DB::listen(function($query) {
    dump($query->sql);
    dump($query->bindings);
});
```

---

## 🔐 SEGURANÇA

### Gerar Chaves

```bash
# Gerar APP_KEY
php artisan key:generate

# Gerar secrets
php artisan tinker
>>> Str::random(32);
```

### Permissões

```bash
# Definir permissões corretas
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Verificar permissões
ls -la storage/
```

---

## 🎯 COMANDOS RÁPIDOS DO DIA A DIA

```bash
# Iniciar tudo
tmux new-session -d -s registro-ponto 'php artisan serve' \; split-window 'php artisan queue:work' \; attach

# Ver status geral
php artisan queue:monitor && php artisan about

# Limpar e otimizar
php artisan optimize:clear && php artisan optimize

# Backup rápido
pg_dump -U postgres registro_ponto > backup-$(date +%Y%m%d-%H%M).sql

# Verificar saúde
curl http://localhost:8000/api/health
```

---

## 💡 DICAS FINAIS

1. **SEMPRE rode o queue worker**
   ```bash
   php artisan queue:work
   ```

2. **Monitore os logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Faça backups regulares**
   ```bash
   # Cron diário
   0 2 * * * pg_dump -U postgres registro_ponto > /backups/registro-$(date +\%Y\%m\%d).sql
   ```

4. **Otimize regularmente**
   ```bash
   php artisan optimize
   ```

5. **Limpe dados antigos**
   ```bash
   php artisan queue:prune-failed --hours=48
   ```

---

**🚀 Sistema pronto para uso em produção!**
