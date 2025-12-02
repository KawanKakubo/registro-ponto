# REFATORAÇÃO: PESSOA + VÍNCULOS (Matrícula Múltipla)

## 📋 CONTEXTO

### Problema Identificado
O modelo atual "1 Colaborador = 1 Matrícula" não atende à realidade do serviço público, onde uma mesma pessoa (CPF único) pode ter múltiplos vínculos (matrículas diferentes).

**Exemplo Real:**
- Dr. João Silva (CPF: 123.456.789-00)
  - Matrícula 1001: Médico 20h (Secretaria de Saúde)
  - Matrícula 1002: Professor 20h (Secretaria de Educação)

### Modelo Anterior (INCORRETO)
```
employees
├── id
├── cpf (UNIQUE)
├── matricula
├── department_id
└── work_shift_template_id

Problema: Não permite múltiplas matrículas por CPF
```

### Modelo Novo (CORRETO)
```
people (Pessoas)
├── id
├── cpf (UNIQUE)
├── full_name
└── pis_pasep

employee_registrations (Vínculos/Matrículas)
├── id
├── person_id (FK → people.id)
├── matricula (UNIQUE)
├── establishment_id
├── department_id
├── admission_date
├── position
└── status

Solução: 1 Pessoa → N Vínculos
```

## 🎯 OBJETIVOS DA REFATORAÇÃO

1. **Separar Dados Pessoais de Dados de Vínculo**
   - Pessoa: CPF, Nome, PIS (únicos por indivíduo)
   - Vínculo: Matrícula, Departamento, Jornada, Data Admissão

2. **Permitir Múltiplas Matrículas por CPF**
   - Uma pessoa pode ter N vínculos ativos simultaneamente
   - Cada vínculo é independente e tem sua própria jornada

3. **Manter Integridade dos Dados**
   - Pontos são registrados por vínculo (matrícula)
   - Cartões de ponto são gerados por vínculo
   - Cada vínculo pode ter jornada diferente

## 🔧 FASES DA IMPLEMENTAÇÃO

### FASE 1: ESTRUTURA DE DADOS ✅

#### Migration Criada
`2025_11_03_085222_rename_employees_to_people_and_create_employee_registrations.php`

**Passos da Migration:**

1. **Renomear** `employees` → `people`

2. **Criar tabela** `employee_registrations`:
```sql
- id
- person_id (FK → people.id)
- matricula (UNIQUE)
- establishment_id (FK)
- department_id (FK, nullable)
- admission_date
- position (nullable)
- status (active/inactive/on_leave)
- timestamps
```

3. **Migrar dados existentes**:
   - Para cada registro em `people` (antiga `employees`):
     - Se tem matrícula: criar registro em `employee_registrations`
     - Vincular ao `person_id` correspondente

4. **Remover colunas** de `people`:
   - matricula
   - establishment_id
   - department_id
   - admission_date
   - position
   - status

5. **Atualizar** `time_records`:
   - Adicionar `employee_registration_id` (FK)
   - Migrar dados: associar ao vínculo correto
   - Remover `employee_id`

6. **Atualizar** `employee_work_shift_assignments`:
   - Adicionar `employee_registration_id` (FK)
   - Migrar dados
   - Remover `employee_id`

7. **Atualizar** `work_schedules`:
   - Adicionar `employee_registration_id` (FK)
   - Migrar dados
   - Remover `employee_id`

#### Models Criados ✅

**Person.php**
```php
- Tabela: people
- Fillable: full_name, cpf, pis_pasep, ctps
- Relacionamentos:
  - hasMany: employeeRegistrations
  - hasMany: activeRegistrations (where status = active)
- Mutators: CPF e PIS formatado
```

**EmployeeRegistration.php**
```php
- Tabela: employee_registrations
- Fillable: person_id, matricula, establishment_id, department_id, admission_date, position, status
- Relacionamentos:
  - belongsTo: person, establishment, department
  - hasMany: timeRecords, workSchedules, workShiftAssignments
  - hasOne: currentWorkShiftAssignment (ativa)
- Scopes: active, fromEstablishment, fromDepartment
```

#### Models Atualizados ✅

**TimeRecord.php**
- Mudou: `employee_id` → `employee_registration_id`
- Relacionamento: `belongsTo employeeRegistration`
- Mantido método `employee()` por compatibilidade (deprecated)

**WorkSchedule.php**
- Mudou: `employee_id` → `employee_registration_id`
- Relacionamento: `belongsTo employeeRegistration`

**EmployeeWorkShiftAssignment.php**
- Mudou: `employee_id` → `employee_registration_id`
- Relacionamento: `belongsTo employeeRegistration`

### FASE 2: LÓGICA DE IMPORTAÇÃO CSV ✅

#### Job: ImportEmployeesFromCsv.php

**Nova Lógica "Inteligente":**

```php
Para cada linha do CSV {
    // PASSO 1: Buscar/Criar PESSOA por CPF
    $person = Person::where('cpf', $cpf)->first();
    
    if (!$person) {
        $person = Person::create([
            'cpf' => $cpf,
            'full_name' => $nome,
            'pis_pasep' => $pis,
        ]);
    }
    
    // PASSO 2: Buscar/Criar VÍNCULO por MATRÍCULA
    $registration = EmployeeRegistration::where('matricula', $matricula)->first();
    
    if (!$registration) {
        EmployeeRegistration::create([
            'person_id' => $person->id,
            'matricula' => $matricula,
            'establishment_id' => $establishment,
            'department_id' => $department,
            'admission_date' => $admission_date,
            'position' => $position,
            'status' => 'active',
        ]);
    } else {
        $registration->update([...]);
    }
}
```

**Resultado:**
- CSV com 3 linhas, mesmo CPF, 3 matrículas diferentes:
  → Cria 1 Person + 3 EmployeeRegistrations

### FASE 3: LÓGICA DE IMPORTAÇÃO AFD ✅

#### BaseAfdParser.php

**Nova Lógica de Identificação:**

```php
findEmployeeRegistration($pis, $matricula, $cpf) {
    // PRIORIDADE 1: Busca direta por Matrícula
    if ($matricula) {
        $registration = EmployeeRegistration::where('matricula', $matricula)
            ->where('status', 'active')
            ->first();
        
        if ($registration) return $registration;
    }
    
    // PRIORIDADE 2: Busca por PIS → Pessoa → Primeiro vínculo ativo
    if ($pis) {
        $person = Person::where('pis_pasep', $pis)->first();
        return $person?->activeRegistrations()->first();
    }
    
    // PRIORIDADE 3: Busca por CPF → Pessoa → Primeiro vínculo ativo
    if ($cpf) {
        $person = Person::where('cpf', $cpf)->first();
        return $person?->activeRegistrations()->first();
    }
    
    return null;
}
```

**createTimeRecord():**
```php
TimeRecord::create([
    'employee_registration_id' => $registration->id,  // Mudou aqui!
    'recorded_at' => $recordedAt,
    'record_date' => $date,
    'record_time' => $time,
    ...
]);
```

**Observação Importante:**
- Se AFD usar PIS e pessoa tiver múltiplos vínculos, retorna o primeiro ativo
- **Ideal**: AFD deveria identificar pela Matrícula (mais específico)
- **Limitação**: Se o relógio só registra PIS, não saberemos qual vínculo bateu ponto

### FASE 4: GERAÇÃO DE CARTÃO DE PONTO (TODO)

#### Novo Fluxo de UI/UX

**Tela 1: Busca de Pessoa**
```
┌──────────────────────────────────────┐
│ Buscar Colaborador                   │
├──────────────────────────────────────┤
│ Nome: [________________] [Buscar]    │
│ CPF:  [________________] [Buscar]    │
└──────────────────────────────────────┘
```

**Tela 2: Seleção de Vínculos**
```
┌──────────────────────────────────────┐
│ Pessoa: JOÃO DA SILVA                │
│ CPF: 123.456.789-00                  │
├──────────────────────────────────────┤
│ Vínculos (Selecione):                │
│                                      │
│ [x] Matrícula 1001 - Professor       │
│     Depto: Educação | Jornada: 30h   │
│                                      │
│ [x] Matrícula 1002 - Motorista       │
│     Depto: Transporte | Jornada: 40h │
│                                      │
│ [ ] Marcar Todos                     │
│                                      │
│ Período: [01/10/2025] a [31/10/2025] │
│                                      │
│ [Gerar Cartões de Ponto]             │
└──────────────────────────────────────┘
```

**Tela 3: Download**
```
┌──────────────────────────────────────┐
│ Cartões Gerados com Sucesso!         │
├──────────────────────────────────────┤
│ 📦 joao_silva_cartoes_out2025.zip    │
│                                      │
│ Contém:                              │
│ - 1001_professor_out2025.pdf         │
│ - 1002_motorista_out2025.pdf         │
│                                      │
│ [Baixar ZIP]                         │
└──────────────────────────────────────┘
```

#### Controller/Service (TODO)

**TimesheetController:**
```php
public function selectPerson() {
    // Buscar pessoa por nome/CPF
    // Retornar pessoa + vínculos ativos
}

public function generateMultiple(Request $request) {
    $personId = $request->person_id;
    $registrationIds = $request->registration_ids; // Array
    $startDate = $request->start_date;
    $endDate = $request->end_date;
    
    $pdfs = [];
    
    foreach ($registrationIds as $regId) {
        $registration = EmployeeRegistration::find($regId);
        
        // Gerar cartão de ponto para este vínculo específico
        $pdf = $this->generatePdf($registration, $startDate, $endDate);
        
        $pdfs[] = [
            'filename' => "{$registration->matricula}_{$registration->position}_{$period}.pdf",
            'content' => $pdf,
        ];
    }
    
    // Criar ZIP com todos os PDFs
    $zipPath = $this->createZip($pdfs, $personId, $period);
    
    return response()->download($zipPath);
}
```

**TimesheetGeneratorService (TODO):**
```php
public function generate(EmployeeRegistration $registration, $startDate, $endDate): array {
    // Mudar assinatura: aceita EmployeeRegistration em vez de Employee
    
    // Buscar pontos deste vínculo específico
    $timeRecords = TimeRecord::where('employee_registration_id', $registration->id)
        ->betweenDates($startDate, $endDate)
        ->get();
    
    // Buscar jornada deste vínculo específico
    $assignment = $registration->currentWorkShiftAssignment;
    
    // Calcular horas extras/faltas para este vínculo
    ...
}
```

### FASE 5: ATUALIZAÇÃO DE CONTROLLERS E VIEWS (TODO)

#### Controllers a Atualizar

**EmployeeController:**
- Listar pessoas + seus vínculos
- Criar pessoa + primeiro vínculo
- Adicionar vínculos a pessoa existente

**WorkShiftTemplateController:**
- Atribuir jornada a vínculo (não a pessoa)
- Bulk assign por vínculos

**TimeRecordController:**
- Registrar ponto manual: selecionar vínculo
- Listar pontos por vínculo

#### Views a Atualizar

**employees/index.blade.php:**
```
┌──────────────────────────────────────────────────────────┐
│ Nome        │ CPF           │ Vínculos                    │
├──────────────────────────────────────────────────────────┤
│ João Silva  │ 123.456.789   │ 1001 (Prof), 1002 (Mot)    │
│ Maria Costa │ 987.654.321   │ 2001 (Méd)                 │
└──────────────────────────────────────────────────────────┘
```

**employees/show.blade.php:**
```
┌──────────────────────────────────────┐
│ Dados Pessoais                       │
├──────────────────────────────────────┤
│ Nome: João Silva                     │
│ CPF: 123.456.789-00                  │
│ PIS: 123.45678.90-1                  │
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│ Vínculos (Matrículas)                │
├──────────────────────────────────────┤
│ Matrícula 1001                       │
│ - Cargo: Professor                   │
│ - Depto: Educação                    │
│ - Jornada: 30h semanais              │
│ - Admissão: 01/03/2020               │
│ [Editar] [Ver Cartão de Ponto]       │
│                                      │
│ Matrícula 1002                       │
│ - Cargo: Motorista                   │
│ - Depto: Transporte                  │
│ - Jornada: 40h semanais              │
│ - Admissão: 15/06/2022               │
│ [Editar] [Ver Cartão de Ponto]       │
│                                      │
│ [+ Adicionar Novo Vínculo]           │
└──────────────────────────────────────┘
```

## 🚨 PONTOS DE ATENÇÃO

### 1. Ambiguidade na Identificação por PIS
**Problema:**
- Relógio AFD registra PIS
- Pessoa tem 2 vínculos ativos
- Qual vínculo bateu ponto?

**Soluções:**
1. **Curto Prazo:** Assumir primeiro vínculo ativo (implementado)
2. **Médio Prazo:** Registrar PIS no ponto e permitir correção manual no sistema
3. **Longo Prazo:** Configurar relógios para usar Matrícula em vez de PIS

### 2. Migração de Dados Existentes
**Cuidado:**
- Dados atuais: 1 employee = 1 matrícula
- Após migration: 1 person = 1 vínculo (inicialmente)
- Importação futura: 1 person = N vínculos

**Teste:**
```sql
-- Verificar integridade após migration
SELECT 
    p.id,
    p.full_name,
    COUNT(er.id) as total_vinculos
FROM people p
LEFT JOIN employee_registrations er ON er.person_id = p.id
GROUP BY p.id
HAVING COUNT(er.id) = 0;  -- Pessoas sem vínculo (ERRO!)
```

### 3. Performance
**Considerações:**
- Joins adicionais: `person → registration → time_records`
- Índices necessários:
  ```sql
  INDEX(employee_registrations.person_id)
  INDEX(employee_registrations.matricula)
  INDEX(time_records.employee_registration_id)
  ```

### 4. Backward Compatibility
**Mantido:**
- `Employee::class` ainda existe (alias para compatibilidade)
- Métodos `employee()` marcados como `@deprecated`
- Gradualmente refatorar código antigo

## 📝 CHECKLIST DE IMPLEMENTAÇÃO

### Fase 1: Estrutura ✅
- [x] Migration criada
- [x] Model Person criado
- [x] Model EmployeeRegistration criado
- [x] TimeRecord atualizado
- [x] WorkSchedule atualizado
- [x] EmployeeWorkShiftAssignment atualizado

### Fase 2: Importação CSV ✅
- [x] ImportEmployeesFromCsv refatorado
- [x] Lógica Pessoa + Vínculo implementada
- [x] Validações atualizadas

### Fase 3: Importação AFD ✅
- [x] BaseAfdParser refatorado
- [x] findEmployeeRegistration() implementado
- [x] createTimeRecord() atualizado
- [x] Parsers específicos (herdam da base)

### Fase 4: Geração de Cartão (TODO)
- [ ] TimesheetController::selectPerson()
- [ ] TimesheetController::generateMultiple()
- [ ] View: busca de pessoa
- [ ] View: seleção de vínculos
- [ ] ZipService para múltiplos PDFs
- [ ] TimesheetGeneratorService refatorado

### Fase 5: Controllers/Views (TODO)
- [ ] EmployeeController refatorado
- [ ] employees/index.blade.php
- [ ] employees/show.blade.php
- [ ] employees/create.blade.php
- [ ] employees/edit.blade.php
- [ ] WorkShiftTemplateController::bulkAssign
- [ ] Todos os controllers que usam Employee

### Fase 6: Testes (TODO)
- [ ] Unit test: Person model
- [ ] Unit test: EmployeeRegistration model
- [ ] Integration test: CSV import
- [ ] Integration test: AFD import
- [ ] Integration test: Timesheet generation
- [ ] E2E test: Fluxo completo

## 🎯 PRÓXIMOS PASSOS

1. **Executar Migration:**
```bash
php artisan migrate
```

2. **Testar Importação CSV:**
```bash
# Importar CSV com mesmos CPFs, matrículas diferentes
# Verificar se cria 1 pessoa + N vínculos
```

3. **Testar Importação AFD:**
```bash
# Importar AFD com matrícula conhecida
# Verificar se associa ao vínculo correto
```

4. **Implementar Fase 4 (Cartão de Ponto):**
   - Criar controller methods
   - Criar views de seleção
   - Implementar geração de ZIP

5. **Refatorar Controllers/Views:**
   - Atualizar um por um
   - Testar cada mudança
   - Manter compatibilidade

## 📚 REFERÊNCIAS

- Migration: `database/migrations/2025_11_03_085222_*.php`
- Models: `app/Models/Person.php`, `app/Models/EmployeeRegistration.php`
- Job: `app/Jobs/ImportEmployeesFromCsv.php`
- Parser: `app/Services/AfdParsers/BaseAfdParser.php`
- Service: `app/Services/TimesheetGeneratorService.php` (TODO)

