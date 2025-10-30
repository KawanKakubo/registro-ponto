# 🚀 GUIA RÁPIDO - SISTEMA DE PONTO

## ⚡ Início Rápido (5 minutos)

### 1️⃣ Iniciar Servidores (2 Terminais Necessários)

#### Terminal 1: Servidor Web
```bash
cd /home/kawan/Documents/areas/SECTI/registro-ponto
php artisan serve
```
**URL**: http://localhost:8000

#### Terminal 2: Queue Worker (OBRIGATÓRIO!)
```bash
cd /home/kawan/Documents/areas/SECTI/registro-ponto
php artisan queue:work --tries=3 --timeout=300
```

**⚠️ IMPORTANTE**: Sem o queue worker, as importações NÃO funcionarão!

---

## 📁 ESTRUTURA DE NAVEGAÇÃO

```
Dashboard (/)
├── 👥 Colaboradores (/employees)
│   ├── Listar (com filtros)
│   ├── Adicionar Novo
│   ├── Editar
│   ├── Ver Detalhes
│   └── Gerar Folha de Ponto
│
├── 🏢 Estabelecimentos (/establishments)
│   ├── Listar
│   ├── Adicionar
│   └── Editar
│
├── 🏭 Departamentos (/departments)
│   ├── Listar
│   ├── Adicionar
│   └── Editar
│
├── ⏰ Escalas de Trabalho (/employees/{id}/work-schedules)
│   ├── Listar
│   └── Adicionar/Editar
│
├── 📥 Importações AFD (/afd-imports)
│   ├── Nova Importação
│   ├── Histórico
│   └── Detalhes
│
└── 📊 Importações CSV (/employee-imports)
    ├── Nova Importação
    ├── Download Modelo
    ├── Histórico
    └── Detalhes
```

---

## 🎯 CASOS DE USO COMUNS

### 1. Importar 50+ Colaboradores

**Tempo: ~3 minutos**

1. Acesse: http://localhost:8000/employee-imports/create
2. Clique em "📄 Baixar Modelo CSV"
3. Abra o modelo no Excel/LibreOffice
4. Preencha os dados:
   ```
   full_name,cpf,pis_pasep,admission_date,establishment_id,department_id,role,email,phone
   João Silva,123.456.789-00,123.45678.90-1,2024-01-15,1,1,Analista,joao@email.com,(11) 98765-4321
   ```
5. Salve como CSV (UTF-8)
6. Faça upload
7. Revise a validação
8. Confirme

**Status**: Veja o progresso em `/employee-imports`

---

### 2. Importar Arquivo AFD

**Tempo: ~1 minuto**

1. Acesse: http://localhost:8000/afd-imports/create
2. Selecione arquivo `.txt` do REP
3. Clique em "Importar"
4. Aguarde processamento (background)

**Resultado**: Registros aparecem em cada colaborador

---

### 3. Gerar Folha de Ponto

**Tempo: ~30 segundos**

1. Acesse: http://localhost:8000/employees
2. Clique no colaborador desejado
3. Clique em "Gerar Folha de Ponto"
4. Selecione período (ex: 01/10/2025 a 31/10/2025)
5. Clique em "Gerar"

**Resultado**: PDF/HTML com folha de ponto formatada

---

### 4. Buscar Colaborador

**Opção A: Busca Rápida**
1. Na listagem, use o campo de busca
2. Digite nome ou CPF
3. Resultados filtrados instantaneamente

**Opção B: Filtros em Cascata**
1. Selecione Estabelecimento
2. Selecione Departamento (carrega apenas os do estabelecimento)
3. Busque pelo nome

---

## 🔍 VERIFICAÇÕES IMPORTANTES

### ✅ Checklist Antes de Começar

```bash
# 1. Banco de dados conectado?
php artisan tinker --execute="DB::connection()->getPdo();"
# ✅ Deve retornar objeto PDO

# 2. Tabelas criadas?
php artisan tinker --execute="echo DB::table('employees')->count();"
# ✅ Deve retornar número (0 ou mais)

# 3. Queue worker rodando?
php artisan queue:monitor
# ✅ Deve mostrar status das filas

# 4. Servidor web rodando?
curl http://localhost:8000
# ✅ Deve retornar HTML
```

---

## 📊 MONITORAMENTO EM TEMPO REAL

### Ver Jobs na Fila
```bash
# Terminal 3
watch -n 1 "php artisan queue:monitor"
```

### Ver Logs
```bash
# Terminal 3
tail -f storage/logs/laravel.log
```

### Ver Jobs Falhados
```bash
php artisan queue:failed
```

---

## 🐛 PROBLEMAS COMUNS

### 1. "Importação não processa"

**Causa**: Queue worker não está rodando
**Solução**:
```bash
php artisan queue:work --tries=3 --timeout=300
```

### 2. "Erro 500 ao importar"

**Causa**: Permissões de diretório
**Solução**:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 3. "CPF inválido"

**Causa**: Formato incorreto
**Solução**: Use `000.000.000-00` (com pontos e hífen)

### 4. "Departamento não encontrado"

**Causa**: ID inválido no CSV
**Solução**: 
1. Acesse `/departments`
2. Copie o ID correto
3. Use no CSV

---

## 📈 DADOS DE EXEMPLO

### Criar Dados de Teste

```bash
# Via Tinker
php artisan tinker
```

```php
// Criar estabelecimento
$est = \App\Models\Establishment::create([
    'trade_name' => 'Empresa Teste',
    'legal_name' => 'Empresa Teste LTDA',
    'cnpj' => '12.345.678/0001-90',
    'cei' => '12.345.67890/12',
    'address' => 'Rua Teste, 123',
    'city' => 'São Paulo',
    'state' => 'SP',
    'zip_code' => '01234-567'
]);

// Criar departamento
$dept = \App\Models\Department::create([
    'name' => 'TI',
    'establishment_id' => $est->id
]);

// Criar colaborador
$emp = \App\Models\Employee::create([
    'full_name' => 'João Teste',
    'cpf' => '123.456.789-00',
    'pis_pasep' => '123.45678.90-1',
    'admission_date' => '2024-01-15',
    'establishment_id' => $est->id,
    'department_id' => $dept->id,
    'role' => 'Desenvolvedor'
]);

echo "✅ Dados criados!\n";
echo "Estabelecimento ID: {$est->id}\n";
echo "Departamento ID: {$dept->id}\n";
echo "Colaborador ID: {$emp->id}\n";
```

---

## 🎓 COMANDOS ÚTEIS

```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reprocessar job falhado
php artisan queue:retry all

# Ver rotas disponíveis
php artisan route:list

# Ver status do sistema
php artisan about

# Criar backup do banco
php artisan db:backup
```

---

## 📞 SUPORTE

**Documentação Completa**: Veja `SYSTEM_ARCHITECTURE.md`
**Logs**: `storage/logs/laravel.log`
**Debug**: Adicione `dd($variable)` no código

---

**Pronto para usar! 🎉**

Qualquer dúvida, verifique a documentação completa ou os logs.
