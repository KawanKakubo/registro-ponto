# ⚡ GUIA RÁPIDO - REFATORAÇÃO PERSON + VÍNCULOS

## 🎯 Conceito Básico

```
ANTES: 1 Employee = 1 pessoa com 1 emprego

AGORA: 1 Person → N EmployeeRegistrations (vínculos)
```

Uma **pessoa** pode ter **múltiplos vínculos** (matrículas) simultâneos ou sequenciais.

---

## 📋 Models Principais

### Person
```php
// Dados pessoais
- id
- full_name
- cpf (único)
- pis_pasep (único, nullable)
- ctps (nullable)
- timestamps

// Relacionamentos
- hasMany('employeeRegistrations')
- hasMany('activeRegistrations') // status = active
```

### EmployeeRegistration
```php
// Dados do vínculo empregatício
- id
- person_id (FK)
- matricula (único)
- establishment_id (FK)
- department_id (FK, nullable)
- position (string, nullable)
- admission_date
- status (enum: active, inactive, on_leave)
- timestamps

// Relacionamentos
- belongsTo('person')
- belongsTo('establishment')
- belongsTo('department')
- hasMany('workShiftAssignments')
- hasOne('currentWorkShiftAssignment') // ativo no momento
- hasMany('timeRecords')
```

---

## 🛣️ Rotas Principais

### Pessoas
```php
GET    /employees              → index (lista)
GET    /employees/create       → create (form)
POST   /employees              → store
GET    /employees/{id}         → show (detalhes)
GET    /employees/{id}/edit    → edit (form)
PUT    /employees/{id}         → update
DELETE /employees/{id}         → destroy
```

### Vínculos
```php
// Criar
GET  /people/{person}/registrations/create → create (form)
POST /people/{person}/registrations        → store

// Editar
GET    /registrations/{id}/edit      → edit (form)
PUT    /registrations/{id}           → update
DELETE /registrations/{id}           → destroy

// Ações especiais
POST /registrations/{id}/terminate   → encerrar (status → inactive)
POST /registrations/{id}/reactivate  → reativar (status → active)
```

### Jornadas
```php
GET  /work-shift-templates/bulk-assign       → form de atribuição
POST /work-shift-templates/bulk-assign       → processar atribuição
```

### Cartões de Ponto
```php
GET  /timesheets                                → buscar pessoa
POST /timesheets/search-person                  → buscar por CPF
GET  /timesheets/person/{id}/registrations      → selecionar vínculos
POST /timesheets/generate-multiple              → gerar múltiplos
GET  /timesheets/registration/{id}              → visualizar um
POST /timesheets/download-zip                   → baixar ZIP
```

---

## 💻 Exemplos de Código

### Buscar pessoa com vínculos
```php
$person = Person::with(['employeeRegistrations', 'activeRegistrations'])
    ->find($id);
```

### Criar pessoa + primeiro vínculo
```php
DB::transaction(function () use ($data) {
    $person = Person::create([
        'full_name' => $data['full_name'],
        'cpf' => $data['cpf'],
        // ...
    ]);
    
    if ($data['create_first_registration']) {
        $person->employeeRegistrations()->create([
            'matricula' => $data['matricula'],
            'establishment_id' => $data['establishment_id'],
            // ...
        ]);
    }
    
    return $person;
});
```

### Buscar vínculos ativos
```php
$registrations = EmployeeRegistration::with([
    'person',
    'establishment',
    'department',
    'currentWorkShiftAssignment.template'
])
->where('status', 'active')
->get();
```

### Atribuir jornada a vínculo
```php
$registration->workShiftAssignments()->create([
    'template_id' => $templateId,
    'effective_from' => $effectiveFrom,
    'cycle_start_date' => $effectiveFrom,
    'effective_until' => null,
    'assigned_by' => auth()->id(),
    'assigned_at' => now(),
]);
```

### Encerrar vínculo (preserva histórico)
```php
$registration->update(['status' => 'inactive']);
```

### Excluir com validação
```php
// Verificar dependências
if ($person->timeRecords()->exists()) {
    return redirect()->back()
        ->with('error', 'Não pode excluir - possui registros de ponto');
}

// Excluir em cascade
$person->delete(); // Exclui person + registrations
```

---

## 🔍 Queries Úteis

### Pessoas sem vínculos ativos
```php
Person::doesntHave('activeRegistrations')->get();
```

### Vínculos sem jornada
```php
EmployeeRegistration::doesntHave('currentWorkShiftAssignment')
    ->where('status', 'active')
    ->get();
```

### Vínculos por estabelecimento
```php
EmployeeRegistration::where('establishment_id', $estId)
    ->with('person')
    ->where('status', 'active')
    ->get();
```

### Registros de ponto de um vínculo
```php
TimeRecord::where('employee_registration_id', $regId)
    ->whereBetween('recorded_at', [$start, $end])
    ->orderBy('recorded_at')
    ->get();
```

---

## 🎨 Views - Estrutura

### employees/index.blade.php
```blade
@foreach($people as $person)
    <tr>
        <td>{{ $person->full_name }}</td>
        <td>{{ $person->cpf_formatted }}</td>
        <td>{{ $person->active_registrations_count }}</td>
        <td>
            @foreach($person->activeRegistrations->take(2) as $reg)
                <span class="badge">{{ $reg->matricula }}</span>
            @endforeach
        </td>
        <td>
            <a href="{{ route('employees.show', $person) }}">Ver</a>
            <a href="{{ route('employees.edit', $person) }}">Editar</a>
        </td>
    </tr>
@endforeach
```

### employees/show.blade.php
```blade
<!-- Dados pessoais -->
<h2>{{ $person->full_name }}</h2>
<p>CPF: {{ $person->cpf_formatted }}</p>

<!-- Lista de vínculos -->
@foreach($person->employeeRegistrations as $registration)
    <div class="registration {{ $registration->status }}">
        <h3>Matrícula: {{ $registration->matricula }}</h3>
        <p>Status: {{ $registration->status }}</p>
        
        @if($registration->currentWorkShiftAssignment)
            <span class="badge-success">
                {{ $registration->currentWorkShiftAssignment->template->name }}
            </span>
        @endif
        
        <a href="{{ route('registrations.edit', $registration) }}">Editar</a>
        
        @if($registration->status === 'active')
            <form action="{{ route('registrations.terminate', $registration) }}" method="POST">
                @csrf
                <button>Encerrar</button>
            </form>
        @else
            <form action="{{ route('registrations.reactivate', $registration) }}" method="POST">
                @csrf
                <button>Reativar</button>
            </form>
        @endif
    </div>
@endforeach
```

---

## 🧪 Testes - Exemplos

### Testar criação de pessoa
```php
public function test_can_create_person(): void
{
    $this->actingAs(User::first());
    
    $response = $this->post(route('employees.store'), [
        'full_name' => 'João Silva',
        'cpf' => '12345678900',
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('people', ['cpf' => '12345678900']);
}
```

### Testar atribuição de jornada
```php
public function test_can_assign_workshift_to_registration(): void
{
    $template = WorkShiftTemplate::first();
    $registration = EmployeeRegistration::first();
    
    $response = $this->post(route('work-shift-templates.bulk-assign.store'), [
        'template_id' => $template->id,
        'registration_ids' => [$registration->id],
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('employee_work_shift_assignments', [
        'template_id' => $template->id,
        'employee_registration_id' => $registration->id,
    ]);
}
```

---

## 🔧 Comandos Úteis

### Executar testes
```bash
# Todos os testes
php artisan test

# Suite específica
php artisan test --filter=EmployeeControllerTest

# Com cobertura
php artisan test --coverage
```

### Gerar dados de teste
```bash
php artisan tinker

# Criar pessoa
$person = Person::create(['full_name' => 'Teste', 'cpf' => '12345678900']);

# Criar vínculo
$person->employeeRegistrations()->create([
    'matricula' => 'MAT001',
    'establishment_id' => 1,
    'admission_date' => now(),
    'status' => 'active'
]);
```

### Migrations
```bash
# Rodar migrations
php artisan migrate

# Reverter última migration
php artisan migrate:rollback

# Resetar banco
php artisan migrate:fresh --seed
```

---

## 🚨 Validações Importantes

### Antes de excluir Person
```php
// Verificar time_records
if ($person->timeRecords()->exists()) {
    return error('Possui registros de ponto');
}

// Verificar registrations
$registrationsCount = $person->employeeRegistrations()->count();
if ($registrationsCount > 0) {
    // Aviso: vai excluir X vínculos
}
```

### Antes de excluir Registration
```php
// Verificar time_records
if ($registration->timeRecords()->exists()) {
    return error('Possui registros de ponto. Prefira encerrar.');
}
```

### Antes de atribuir jornada
```php
// Encerrar atribuições antigas
$registration->workShiftAssignments()
    ->whereNull('effective_until')
    ->update(['effective_until' => now()->subDay()]);

// Criar nova
$registration->workShiftAssignments()->create([...]);
```

---

## �� Estatísticas Úteis

### Dashboard
```php
// Total de pessoas
$totalPeople = Person::count();

// Total de vínculos ativos
$activeRegistrations = EmployeeRegistration::where('status', 'active')->count();

// Pessoas sem vínculos ativos
$peopleWithoutRegistrations = Person::doesntHave('activeRegistrations')->count();

// Vínculos sem jornada
$registrationsWithoutWorkshift = EmployeeRegistration::doesntHave('currentWorkShiftAssignment')
    ->where('status', 'active')
    ->count();

// Vínculos por estabelecimento
$byEstablishment = EmployeeRegistration::where('status', 'active')
    ->groupBy('establishment_id')
    ->selectRaw('establishment_id, count(*) as total')
    ->with('establishment')
    ->get();
```

---

## 🎯 Padrões de Nomenclatura

### Variáveis
```php
$person              // Uma pessoa
$people              // Múltiplas pessoas
$registration        // Um vínculo
$registrations       // Múltiplos vínculos
$establishment       // Um estabelecimento
$template            // Um template de jornada
```

### Métodos
```php
getPeopleWithActiveRegistrations()
createPersonWithFirstRegistration()
assignWorkShiftToRegistrations()
terminateRegistration()
reactivateRegistration()
```

### Rotas
```php
employees.index
employees.show
registrations.create
registrations.terminate
work-shift-templates.bulk-assign
timesheets.person-registrations
```

---

## 📚 Documentação Completa

Para mais detalhes, consulte:
- **FASE5_CONCLUIDA.md** - Controllers e Views detalhados
- **FASE6_CONCLUIDA.md** - WorkShift Template detalhado
- **STATUS_ATUAL.md** - Status consolidado
- **RESUMO_VISUAL.md** - Resumo visual
- **TODO_REFATORACAO.md** - Lista de tarefas

---

**Última Atualização**: $(date +"%d/%m/%Y %H:%M")  
**Versão**: 1.0  
**Status**: ✅ Operacional
