# ❓ FAQ - Registros de Ponto (Batidas AFD)

## 🎯 Pergunta Principal

### **Ao importar registros AFD, eles são associados à PESSOA ou ao VÍNCULO?**

**RESPOSTA:** Os registros de ponto são associados ao **VÍNCULO EMPREGATÍCIO (matrícula)**, não diretamente à pessoa.

---

## 📊 Estrutura no Banco de Dados

```sql
-- Tabela: time_records
CREATE TABLE time_records (
    id BIGINT PRIMARY KEY,
    employee_registration_id BIGINT NOT NULL,  -- ← FK para VÍNCULO
    recorded_at TIMESTAMP,
    record_date DATE,
    record_time TIME,
    nsr VARCHAR(20),
    imported_from_afd BOOLEAN,
    afd_file_name VARCHAR(255),
    
    FOREIGN KEY (employee_registration_id) 
        REFERENCES employee_registrations(id)
);
```

**Observe:** A coluna é `employee_registration_id`, não `person_id`.

---

## 🔄 Como Funciona na Prática

### Cenário 1: Pessoa com 1 Vínculo (Caso Simples)

```
┌─────────────────┐
│  PESSOA: João   │
│  CPF: 123...    │
│  PIS: 987...    │
└────────┬────────┘
         │ tem 1 vínculo
         ▼
┌─────────────────────────────┐
│  VÍNCULO 1                  │
│  Matrícula: 1001            │
│  Estabelecimento: Matriz    │
│  Departamento: TI           │
└────────┬────────────────────┘
         │ tem vários registros
         ▼
┌────────────────────────────────────────┐
│  REGISTRO DE PONTO                     │
│  employee_registration_id: 1 (Vínculo 1) │
│  Data: 01/12/2024 08:00                │
│  Importado do AFD: Sim                 │
└────────────────────────────────────────┘
```

**Neste caso:**
- AFD contém PIS `987...`
- Sistema encontra a pessoa João
- João tem apenas 1 vínculo ativo
- Registro é associado ao vínculo `1001` da Matriz

---

### Cenário 2: Pessoa com Múltiplos Vínculos (Importante!)

```
┌─────────────────┐
│  PESSOA: Maria  │
│  CPF: 456...    │
│  PIS: 321...    │
└────────┬────────┘
         │ tem 2 vínculos
         ├──────────┬──────────┐
         ▼          ▼          ▼
┌──────────────┐ ┌──────────────┐
│  VÍNCULO 1   │ │  VÍNCULO 2   │
│  Mat: 2001   │ │  Mat: 3001   │
│  Estab: SP   │ │  Estab: RJ   │
│  Depto: RH   │ │  Depto: Fin  │
└──────┬───────┘ └──────┬───────┘
       │                │
       ▼                ▼
   Registros SP     Registros RJ
```

**Quando importar AFD:**

#### Se AFD contém MATRÍCULA:
```
AFD: Matrícula 2001, Data 01/12/2024, 08:00
↓
Sistema busca vínculo pela matrícula 2001
↓
Associa registro ao VÍNCULO 1 (SP - RH) ✅
```

#### Se AFD contém apenas PIS (sem matrícula):
```
AFD: PIS 321..., Data 01/12/2024, 08:00
↓
Sistema encontra pessoa Maria
↓
Maria tem 2 vínculos ativos
↓
⚠️ Sistema pega o PRIMEIRO vínculo ativo (pode não ser o correto!)
↓
Associa ao VÍNCULO 1 (SP) - mas batida pode ser do VÍNCULO 2 (RJ)
```

**⚠️ ATENÇÃO:** Este é o único cenário onde pode haver ambiguidade!

---

## 🔍 Processo de Importação AFD

### Passo a Passo Técnico

```php
// 1. AFD é detectado (formato automático)
$parser = AfdParserFactory::detect($filePath);

// 2. Parser lê cada linha do AFD
foreach ($lines as $line) {
    // Extrai dados da linha
    $data = $parser->parseLine($line);
    // $data contém: PIS ou CPF ou Matrícula + Data/Hora
    
    // 3. BUSCA INTELIGENTE DE VÍNCULO
    $vinculo = BaseAfdParser::findEmployeeRegistration(
        pis: $data['pis'],
        matricula: $data['matricula'],
        cpf: $data['cpf']
    );
    
    // 4. CRIA REGISTRO DE PONTO VINCULADO À MATRÍCULA
    TimeRecord::create([
        'employee_registration_id' => $vinculo->id,  // ← VÍNCULO!
        'recorded_at' => $data['datetime'],
        'record_date' => $data['date'],
        'record_time' => $data['time'],
        'imported_from_afd' => true,
        'afd_file_name' => $fileName,
    ]);
}
```

---

## 🎯 Lógica de Busca de Vínculo

### Ordem de Prioridade

```php
// PRIORIDADE 1: Matrícula (Melhor opção - sem ambiguidade)
if ($matricula) {
    return EmployeeRegistration::where('matricula', $matricula)
        ->where('status', 'active')
        ->first();
}

// PRIORIDADE 2: PIS
if ($pis) {
    $pessoa = Person::where('pis_pasep', $pis)->first();
    
    if ($pessoa) {
        // ⚠️ Se pessoa tem múltiplos vínculos, pega o primeiro ativo
        return $pessoa->activeRegistrations()->first();
    }
}

// PRIORIDADE 3: CPF
if ($cpf) {
    $pessoa = Person::where('cpf', $cpf)->first();
    
    if ($pessoa) {
        // ⚠️ Se pessoa tem múltiplos vínculos, pega o primeiro ativo
        return $pessoa->activeRegistrations()->first();
    }
}
```

---

## 💡 Por Que Associar ao Vínculo e Não à Pessoa?

### Razões Técnicas

1. **Departamento/Estabelecimento Correto**
   - Cada vínculo pertence a um departamento específico
   - Cartão ponto precisa saber em qual estabelecimento a pessoa trabalhou
   
2. **Jornada de Trabalho Específica**
   - Cada vínculo pode ter uma jornada diferente
   - Exemplo: Maria trabalha 8h/dia na Matriz (vínculo 1) e 6h/dia na Filial (vínculo 2)
   
3. **Cálculo de Horas Correto**
   - Horas extras/faltas são calculadas baseadas na jornada do vínculo
   - Pessoa pode ter jornadas diferentes em cada estabelecimento

4. **Relatórios por Estabelecimento**
   - Empresa precisa saber horas trabalhadas por estabelecimento
   - Não faz sentido misturar batidas de estabelecimentos diferentes

---

## 📋 Exemplos Práticos

### Exemplo 1: João - 1 Vínculo

**Dados:**
- Pessoa: João da Silva
- PIS: 12345678901
- Vínculo: Matrícula 1001 - Matriz - TI

**AFD Importado:**
```
12345678901|01/12/2024 08:00
12345678901|01/12/2024 12:00
12345678901|01/12/2024 13:00
12345678901|01/12/2024 17:00
```

**Resultado:**
```sql
INSERT INTO time_records (employee_registration_id, recorded_at)
VALUES 
  (1, '2024-12-01 08:00:00'),  -- Vínculo 1001
  (1, '2024-12-01 12:00:00'),  -- Vínculo 1001
  (1, '2024-12-01 13:00:00'),  -- Vínculo 1001
  (1, '2024-12-01 17:00:00');  -- Vínculo 1001
```

✅ **Tudo correto!** João tem apenas 1 vínculo.

---

### Exemplo 2: Maria - 2 Vínculos (COM matrícula no AFD)

**Dados:**
- Pessoa: Maria Santos
- PIS: 98765432100
- Vínculo 1: Matrícula 2001 - SP - RH
- Vínculo 2: Matrícula 3001 - RJ - Financeiro

**AFD Importado (formato com matrícula):**
```
2001|01/12/2024 08:00
2001|01/12/2024 17:00
3001|02/12/2024 09:00
3001|02/12/2024 18:00
```

**Resultado:**
```sql
INSERT INTO time_records (employee_registration_id, recorded_at)
VALUES 
  (5, '2024-12-01 08:00:00'),  -- Vínculo 2001 (SP) ✅
  (5, '2024-12-01 17:00:00'),  -- Vínculo 2001 (SP) ✅
  (8, '2024-12-02 09:00:00'),  -- Vínculo 3001 (RJ) ✅
  (8, '2024-12-02 18:00:00');  -- Vínculo 3001 (RJ) ✅
```

✅ **Perfeito!** Sistema identifica corretamente cada vínculo pela matrícula.

---

### Exemplo 3: Maria - 2 Vínculos (SEM matrícula no AFD) ⚠️

**AFD Importado (apenas PIS):**
```
98765432100|01/12/2024 08:00  -- Batida em SP
98765432100|01/12/2024 17:00  -- Batida em SP
98765432100|02/12/2024 09:00  -- Batida em RJ
98765432100|02/12/2024 18:00  -- Batida em RJ
```

**Resultado:**
```sql
-- ⚠️ PROBLEMA: Todas as batidas vão para o primeiro vínculo ativo!
INSERT INTO time_records (employee_registration_id, recorded_at)
VALUES 
  (5, '2024-12-01 08:00:00'),  -- Vínculo 2001 (SP) ✅ Correto
  (5, '2024-12-01 17:00:00'),  -- Vínculo 2001 (SP) ✅ Correto
  (5, '2024-12-02 09:00:00'),  -- Vínculo 2001 (SP) ❌ Deveria ser RJ!
  (5, '2024-12-02 18:00:00');  -- Vínculo 2001 (SP) ❌ Deveria ser RJ!
```

❌ **Problema:** Batidas do dia 02/12 (RJ) foram associadas ao vínculo de SP!

---

## ✅ Soluções para Múltiplos Vínculos

### Solução 1: Usar AFD com Matrícula (Recomendado)

Configure o relógio de ponto para exportar a matrícula no arquivo AFD.

**Formatos que suportam matrícula:**
- Henry Orion 5 ✅
- Alguns modelos DIXI ✅

### Solução 2: Importar AFD por Estabelecimento

Adicione filtro na importação para especificar o estabelecimento:

```php
// Ao importar, usuário seleciona estabelecimento
$afdImport->establishment_id = 1; // SP

// Na busca de vínculo
if ($pis && $establishmentId) {
    $pessoa = Person::where('pis_pasep', $pis)->first();
    
    return $pessoa->activeRegistrations()
        ->where('establishment_id', $establishmentId)
        ->first();
}
```

### Solução 3: Importar AFDs Separados

- Importar AFD da Matriz separadamente
- Importar AFD da Filial separadamente
- Cada arquivo já contém apenas batidas daquele estabelecimento

---

## 🎓 Resumo Final

### ✅ O Que Você Precisa Saber

1. **Registros de ponto são vinculados à MATRÍCULA (vínculo), não à pessoa**

2. **Por quê?**
   - Cada vínculo tem seu próprio departamento
   - Cada vínculo pode ter jornada diferente
   - Cartão ponto precisa separar horas por estabelecimento

3. **Como funciona na prática:**
   - AFD com matrícula → Associação perfeita ✅
   - AFD com PIS/CPF + 1 vínculo → Funciona bem ✅
   - AFD com PIS/CPF + múltiplos vínculos → Cuidado! ⚠️

4. **Recomendação:**
   - Se possível, use AFD com matrícula
   - Se não for possível, importe AFDs por estabelecimento
   - Ou implemente filtro de estabelecimento na importação

---

## 📚 Referências Técnicas

**Model TimeRecord:**
```php
class TimeRecord extends Model
{
    protected $fillable = [
        'employee_registration_id',  // ← Vínculo, não pessoa!
        'recorded_at',
        // ...
    ];
    
    public function employeeRegistration(): BelongsTo
    {
        return $this->belongsTo(EmployeeRegistration::class);
    }
}
```

**Estrutura no Banco:**
```sql
time_records.employee_registration_id 
    → employee_registrations.id 
    → employee_registrations.person_id 
    → people.id
```

---

**Documentação criada em:** 02/12/2025  
**Última atualização:** 02/12/2025
