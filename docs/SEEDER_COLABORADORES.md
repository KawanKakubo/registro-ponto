# 🌱 SEEDER - Importação de Colaboradores

## 📋 Descrição

Seeder para importar colaboradores e seus vínculos empregatícios a partir de um arquivo CSV.

**Arquivo:** `database/seeders/EmployeesFromCsvSeeder.php`

---

## 🚀 Como Usar

### 1. Preparar o Arquivo CSV

Coloque o arquivo `importacao-colaboradores.csv` na **raiz do projeto**.

**Estrutura do CSV:**
```csv
full_name,cpf,pis_pasep,matricula,establishment_id,department_id,admission_date,role
JOÃO DA SILVA,123.456.789-01,12345678901,1001,1,5,2020-01-15,PROFESSOR
MARIA SANTOS,987.654.321-00,98765432100,1002,1,5,2020-02-01,COORDENADOR
```

**Campos:**
- `full_name`: Nome completo do colaborador
- `cpf`: CPF (com ou sem formatação)
- `pis_pasep`: PIS/PASEP (com ou sem formatação)
- `matricula`: Número da matrícula (identificador único do vínculo)
- `establishment_id`: ID do estabelecimento (deve existir no banco)
- `department_id`: ID do departamento (opcional, pode estar vazio)
- `admission_date`: Data de admissão (formato: YYYY-MM-DD)
- `role`: Cargo/função

---

### 2. Executar o Seeder

```bash
# Executar apenas este seeder
php artisan db:seed --class=EmployeesFromCsvSeeder

# Ou adicionar ao DatabaseSeeder e executar tudo
php artisan db:seed
```

---

## 🔍 Como Funciona

### Processo de Importação

```
┌─────────────────────────────────────────┐
│  1. Ler arquivo CSV linha por linha     │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│  2. Limpar CPF e PIS (remover formato)  │
│     • CPF: 123.456.789-01 → 12345678901 │
│     • PIS: 123.456.789-0 → 12345678900  │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│  3. Buscar PESSOA por CPF               │
│     ├─ Não existe? → Criar pessoa       │
│     └─ Já existe? → Usar existente      │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│  4. Buscar VÍNCULO por Matrícula        │
│     ├─ Não existe? → Criar vínculo      │
│     └─ Já existe? → Atualizar vínculo   │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│  5. Exibir estatísticas                 │
└─────────────────────────────────────────┘
```

---

## 📊 Lógica de Importação

### Pessoas (CPF como chave única)

```php
// Busca pessoa pelo CPF
$person = Person::where('cpf', $cpf)->first();

if (!$person) {
    // CPF não existe → Criar nova pessoa
    $person = Person::create([
        'full_name' => 'João da Silva',
        'cpf' => '12345678901',
        'pis_pasep' => '12345678901',
    ]);
} else {
    // CPF já existe → Usar pessoa existente
    // Atualizar PIS se estiver vazio
    if (empty($person->pis_pasep)) {
        $person->update(['pis_pasep' => '12345678901']);
    }
}
```

**Resultado:** Uma pessoa não será duplicada mesmo que apareça múltiplas vezes no CSV.

---

### Vínculos (Matrícula como chave única)

```php
// Busca vínculo pela Matrícula
$registration = EmployeeRegistration::where('matricula', $matricula)->first();

if (!$registration) {
    // Matrícula não existe → Criar novo vínculo
    $registration = EmployeeRegistration::create([
        'person_id' => $person->id,
        'matricula' => '1001',
        'establishment_id' => 1,
        'department_id' => 5,
        'admission_date' => '2020-01-15',
        'position' => 'PROFESSOR',
        'status' => 'active',
    ]);
} else {
    // Matrícula já existe → Atualizar vínculo
    $registration->update([
        'person_id' => $person->id,
        'establishment_id' => 1,
        'department_id' => 5,
        'admission_date' => '2020-01-15',
        'position' => 'PROFESSOR',
    ]);
}
```

**Resultado:** Cada matrícula é um vínculo único. Se a matrícula já existe, apenas atualiza os dados.

---

## 🎯 Cenários Práticos

### Cenário 1: Pessoa Nova, Vínculo Novo

**CSV:**
```csv
João Silva,123.456.789-01,12345678901,1001,1,5,2020-01-15,PROFESSOR
```

**Resultado:**
```
✅ Pessoa criada: João Silva (CPF: 12345678901)
   └─ Vínculo criado: Matrícula 1001 - PROFESSOR
```

**No Banco:**
- 1 pessoa criada
- 1 vínculo criado

---

### Cenário 2: Pessoa Existente, Novo Vínculo (Múltiplos Empregos)

**Banco já tem:**
- Pessoa: Maria Santos (CPF: 987.654.321-00)
- Vínculo: Matrícula 2001 - Estabelecimento A

**CSV:**
```csv
Maria Santos,987.654.321-00,98765432100,3001,2,8,2023-01-01,COORDENADOR
```

**Resultado:**
```
ℹ️  Pessoa já existe: Maria Santos
   └─ Vínculo criado: Matrícula 3001 - COORDENADOR
```

**No Banco:**
- 0 pessoas criadas (já existia)
- 1 vínculo criado (novo vínculo no Estabelecimento B)
- Maria agora tem 2 vínculos ativos!

---

### Cenário 3: Atualização de Vínculo Existente

**Banco já tem:**
- Pessoa: João Silva
- Vínculo: Matrícula 1001 - PROFESSOR (Depto 5)

**CSV:**
```csv
João Silva,123.456.789-01,12345678901,1001,1,8,2020-01-15,DIRETOR
```

**Resultado:**
```
ℹ️  Pessoa já existe: João Silva
   └─ Vínculo atualizado: Matrícula 1001
```

**No Banco:**
- 0 pessoas criadas
- 0 vínculos criados
- 1 vínculo atualizado:
  - Cargo mudou: PROFESSOR → DIRETOR
  - Departamento mudou: 5 → 8

---

### Cenário 4: Mesmo CPF, Matrícula Diferente (CSV com duplicatas)

**CSV:**
```csv
João Silva,123.456.789-01,12345678901,1001,1,5,2020-01-15,PROFESSOR
João Silva,123.456.789-01,12345678901,1001,1,5,2020-02-09,PROFESSOR (1)
```

**Resultado:**
```
✅ Pessoa criada: João Silva
   └─ Vínculo criado: Matrícula 1001 - PROFESSOR
ℹ️  Pessoa já existe: João Silva
   └─ Vínculo atualizado: Matrícula 1001 (cargo atualizado)
```

**Observação:** Como a matrícula é a mesma (1001), o segundo registro atualiza o primeiro.

---

## 📈 Saída do Seeder

### Durante a Execução

```
📂 Lendo arquivo CSV...
✅ Pessoa criada: ADALTO GUADAGUINI (CPF: 66724562953)
   └─ Vínculo criado: Matrícula 3292 - AGENTE DE MAQUINAS E VEICULOS - MOTORISTA
✅ Pessoa criada: ADALTON ROSA ARAUJO (CPF: 65325958968)
   └─ Vínculo criado: Matrícula 3915 - AGENTE DE MAQUINAS E VEICULOS - MOTORISTA
✅ Pessoa criada: ADELITA SOARES BASTOS (CPF: 05737558924)
   └─ Vínculo criado: Matrícula 3002 - AGENTE DE COMBATE ÀS ENDEMIAS
...
```

### Estatísticas Finais

```
═══════════════════════════════════════════════
�� ESTATÍSTICAS DA IMPORTAÇÃO
═══════════════════════════════════════════════
┌──────────────────────────┬────────────┐
│ Métrica                  │ Quantidade │
├──────────────────────────┼────────────┤
│ Linhas processadas       │ 350        │
│ Pessoas criadas          │ 280        │
│ Pessoas já existentes    │ 70         │
│ Vínculos criados         │ 345        │
│ Vínculos atualizados     │ 5          │
│ Erros                    │ 0          │
└──────────────────────────┴────────────┘

✅ Importação concluída com sucesso!
```

---

## 🛡️ Tratamento de Erros

### Erros Comuns

#### 1. Arquivo não encontrado
```
❌ Arquivo não encontrado: /path/to/importacao-colaboradores.csv
📝 Coloque o arquivo 'importacao-colaboradores.csv' na raiz do projeto
```

**Solução:** Coloque o arquivo CSV na raiz do projeto Laravel.

#### 2. Estabelecimento inválido
```
⚠️  ERROS ENCONTRADOS:
  • Linha 25: ID do estabelecimento inválido
```

**Solução:** Verifique se o `establishment_id` existe no banco de dados.

#### 3. Nome ou CPF vazio
```
⚠️  ERROS ENCONTRADOS:
  • Linha 42: Nome ou CPF vazio
```

**Solução:** Certifique-se de que todas as linhas têm nome e CPF preenchidos.

---

## 🔧 Recursos Avançados

### Transaction (Rollback em Caso de Erro)

```php
DB::beginTransaction();
try {
    // Processar CSV
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack(); // Desfaz tudo em caso de erro crítico
    throw $e;
}
```

**Benefício:** Se ocorrer um erro fatal, nenhum dado é inserido parcialmente.

---

### Normalização Automática

#### CPF
```php
// Entrada: 123.456.789-01
// Saída:   12345678901

// Entrada: 03992.859-37  (com formatação estranha)
// Saída:   03992859037   (completa com zeros à esquerda)
```

#### PIS/PASEP
```php
// Entrada: 123.456.789-0
// Saída:   12345678900

// Entrada: 1234567890
// Saída:   01234567890 (completa com zeros à esquerda se necessário)
```

---

## 📝 Logs

Erros são registrados no log do Laravel:

```php
Log::error("Erro ao processar linha 42: ...");
```

**Ver logs:**
```bash
tail -f storage/logs/laravel.log
```

---

## ✅ Checklist de Verificação

Antes de executar o seeder:

- [ ] Arquivo CSV está na raiz do projeto
- [ ] Estabelecimentos foram criados no banco (ID 1, 2, 3, etc)
- [ ] Departamentos foram criados (se você usar department_id)
- [ ] Formato do CSV está correto (com cabeçalho)
- [ ] CPFs estão válidos (11 dígitos após limpeza)

Após executar o seeder:

- [ ] Verificar estatísticas (pessoas criadas, vínculos, erros)
- [ ] Consultar banco: `SELECT COUNT(*) FROM people;`
- [ ] Consultar banco: `SELECT COUNT(*) FROM employee_registrations;`
- [ ] Testar login com um colaborador importado
- [ ] Gerar cartão ponto de teste

---

## 🧪 Testando

### Ver quantas pessoas foram criadas
```bash
php artisan tinker
>>> App\Models\Person::count()
=> 280
```

### Ver quantos vínculos foram criados
```bash
>>> App\Models\EmployeeRegistration::count()
=> 350
```

### Ver pessoa específica com seus vínculos
```bash
>>> $pessoa = App\Models\Person::where('cpf', '66724562953')->first()
>>> $pessoa->full_name
=> "ADALTO GUADAGUINI"
>>> $pessoa->employeeRegistrations->count()
=> 1
>>> $pessoa->employeeRegistrations->first()->matricula
=> "3292"
```

---

## 🎉 Pronto!

Agora você tem todos os colaboradores e vínculos importados no banco de dados!

**Próximos passos:**
1. Importar arquivos AFD com registros de ponto
2. Atribuir jornadas de trabalho aos vínculos
3. Gerar cartões ponto

---

**Documentação criada em:** 02/12/2025  
**Última atualização:** 02/12/2025
