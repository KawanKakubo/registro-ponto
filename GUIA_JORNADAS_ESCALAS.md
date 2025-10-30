# 🚀 Guia Rápido - Módulo de Jornadas e Escalas

## ✅ Status da Implementação

### Concluído:
- ✅ Migrations (5 tabelas criadas)
- ✅ Modelos Eloquent (4 modelos + relacionamentos)
- ✅ Seeders (6 presets pré-configurados)
- ✅ Services (3 serviços de lógica de negócio)

### Pendente:
- ⏳ Controllers (WorkShiftTemplateController, WorkShiftAssignmentController)
- ⏳ Rotas (routes/web.php)
- ⏳ Views (Blade templates)
- ⏳ Testes automatizados

---

## 📦 Estrutura Criada

### Tabelas do Banco de Dados:
1. `work_shift_templates` - Templates/modelos de jornada
2. `template_weekly_schedules` - Horários semanais dos templates
3. `template_rotating_rules` - Regras de escalas rotativas
4. `employee_work_shift_assignments` - Atribuições de jornadas aos colaboradores
5. `work_schedules` - Adicionado campo `source_template_id` (compatibilidade)

### Modelos:
- `WorkShiftTemplate`
- `TemplateWeeklySchedule`
- `TemplateRotatingRule`
- `EmployeeWorkShiftAssignment`

### Services:
- `RotatingShiftCalculatorService` - Cálculos de escalas rotativas
- `WorkShiftTemplateService` - Gerenciamento de templates
- `WorkShiftAssignmentService` - Atribuição de jornadas

---

## 🎯 Presets Disponíveis

Os seguintes presets já estão cadastrados no banco de dados:

1. **Comercial (44h/semana)**
   - Seg-Sex: 08:00-12:00 | 13:00-18:00 (8h/dia)
   - Sábado: 08:00-12:00 (4h)
   - Domingo: Folga

2. **Administrativo (40h/semana)**
   - Seg-Sex: 08:00-12:00 | 13:00-17:00 (8h/dia)
   - Sáb-Dom: Folga

3. **Escala 12x36 Noturno**
   - 1 dia de trabalho (12h) por 1 dia de descanso
   - Turno: 19:00 às 07:00

4. **Escala 12x36 Diurno**
   - 1 dia de trabalho (12h) por 1 dia de descanso
   - Turno: 07:00 às 19:00

5. **Escala 6x1**
   - 6 dias de trabalho por 1 dia de folga
   - Turno: 08:00 às 17:00 (8h/dia)

6. **Escala 4x2**
   - 4 dias de trabalho por 2 dias de folga
   - Turno: 08:00 às 16:00 (8h/dia)

---

## 💻 Exemplos de Uso (PHP/Artisan Tinker)

### 1. Listar todos os templates

```php
use App\Services\WorkShiftTemplateService;

$service = app(WorkShiftTemplateService::class);
$templates = $service->getTemplatesWithStats();

foreach ($templates as $template) {
    echo "{$template->name} - {$template->type_formatted} - {$template->active_employees_count} colaboradores\n";
}
```

### 2. Criar um novo template semanal

```php
use App\Services\WorkShiftTemplateService;

$service = app(WorkShiftTemplateService::class);

$data = [
    'name' => 'Meu Template Personalizado',
    'description' => 'Descrição do template',
    'type' => 'weekly',
    'weekly_hours' => 40.00,
    'weekly_schedules' => [
        // Segunda a Sexta
        ['day_of_week' => 1, 'entry_1' => '09:00:00', 'exit_1' => '13:00:00', 'entry_2' => '14:00:00', 'exit_2' => '18:00:00', 'is_work_day' => true],
        ['day_of_week' => 2, 'entry_1' => '09:00:00', 'exit_1' => '13:00:00', 'entry_2' => '14:00:00', 'exit_2' => '18:00:00', 'is_work_day' => true],
        ['day_of_week' => 3, 'entry_1' => '09:00:00', 'exit_1' => '13:00:00', 'entry_2' => '14:00:00', 'exit_2' => '18:00:00', 'is_work_day' => true],
        ['day_of_week' => 4, 'entry_1' => '09:00:00', 'exit_1' => '13:00:00', 'entry_2' => '14:00:00', 'exit_2' => '18:00:00', 'is_work_day' => true],
        ['day_of_week' => 5, 'entry_1' => '09:00:00', 'exit_1' => '13:00:00', 'entry_2' => '14:00:00', 'exit_2' => '18:00:00', 'is_work_day' => true],
        // Fim de semana - folga
        ['day_of_week' => 6, 'is_work_day' => false],
        ['day_of_week' => 0, 'is_work_day' => false],
    ]
];

$template = $service->createTemplate($data);
echo "Template criado: {$template->name}\n";
```

### 3. Criar template de escala rotativa

```php
use App\Services\WorkShiftTemplateService;

$service = app(WorkShiftTemplateService::class);

$data = [
    'name' => 'Minha Escala 12x36',
    'description' => 'Escala personalizada',
    'type' => 'rotating_shift',
    'weekly_hours' => 42.00,
    'rotating_rule' => [
        'work_days' => 1,
        'rest_days' => 1,
        'shift_start_time' => '20:00:00',
        'shift_end_time' => '08:00:00',
    ]
];

$template = $service->createTemplate($data);
echo "Escala criada: {$template->name}\n";
```

### 4. Atribuir jornada a colaboradores

```php
use App\Services\WorkShiftAssignmentService;

$service = app(WorkShiftAssignmentService::class);

// IDs dos colaboradores (exemplo)
$employeeIds = [1, 2, 3, 4, 5];

// ID do template
$templateId = 1; // Comercial (44h/semana)

// Datas
$dates = [
    'effective_from' => '2025-11-01',
    'effective_until' => null, // null = sem fim
    'cycle_start_date' => null, // Só para escalas rotativas
    'generate_schedules' => false, // Gerar registros em work_schedules?
];

$result = $service->assignToEmployees($templateId, $employeeIds, $dates);

echo "Atribuído a {$result['assigned_count']} de {$result['total_count']} colaboradores\n";
```

### 5. Atribuir escala rotativa com data de ciclo

```php
use App\Services\WorkShiftAssignmentService;

$service = app(WorkShiftAssignmentService::class);

$employeeIds = [10, 11, 12];
$templateId = 3; // Escala 12x36

$dates = [
    'effective_from' => '2025-11-01',
    'effective_until' => null,
    'cycle_start_date' => '2025-11-01', // IMPORTANTE para escalas rotativas!
];

$result = $service->assignToEmployees($templateId, $employeeIds, $dates);
```

### 6. Consultar horário de um colaborador em uma data específica

```php
use App\Services\WorkShiftAssignmentService;

$service = app(WorkShiftAssignmentService::class);

$employeeId = 1;
$date = '2025-11-15';

$schedule = $service->getEmployeeScheduleForDate($employeeId, $date);

if ($schedule) {
    echo "Colaborador trabalha neste dia:\n";
    echo "Entrada 1: {$schedule['entry_1']}\n";
    echo "Saída 1: {$schedule['exit_1']}\n";
    echo "Total de horas: {$schedule['daily_hours']}h\n";
} else {
    echo "Colaborador está de folga neste dia.\n";
}
```

### 7. Verificar se uma data é dia de trabalho (escala rotativa)

```php
use App\Services\RotatingShiftCalculatorService;

$calculator = app(RotatingShiftCalculatorService::class);

$targetDate = new DateTime('2025-11-15');
$cycleStartDate = new DateTime('2025-11-01');
$workDays = 1; // 12x36
$restDays = 1;

$isWorking = $calculator->isWorkingDay($targetDate, $cycleStartDate, $workDays, $restDays);

echo $isWorking ? "Dia de trabalho" : "Dia de folga";
```

### 8. Calcular dias de trabalho em um mês (escala rotativa)

```php
use App\Services\RotatingShiftCalculatorService;

$calculator = app(RotatingShiftCalculatorService::class);

$year = 2025;
$month = 11;
$cycleStartDate = new DateTime('2025-11-01');
$workDays = 6; // Escala 6x1
$restDays = 1;

$workingDaysCount = $calculator->getWorkingDaysInMonth($year, $month, $cycleStartDate, $workDays, $restDays);

echo "Dias de trabalho em novembro/2025: {$workingDaysCount} dias\n";
```

### 9. Duplicar um template existente

```php
use App\Services\WorkShiftTemplateService;

$service = app(WorkShiftTemplateService::class);

$originalId = 1; // Template Comercial
$newName = 'Comercial Personalizado - Matriz';

$newTemplate = $service->duplicateTemplate($originalId, $newName);

echo "Template duplicado: {$newTemplate->name}\n";
```

### 10. Ver histórico de jornadas de um colaborador

```php
use App\Services\WorkShiftAssignmentService;

$service = app(WorkShiftAssignmentService::class);

$employeeId = 1;
$history = $service->getEmployeeHistory($employeeId);

foreach ($history as $assignment) {
    echo "{$assignment->template->name} - ";
    echo "De {$assignment->effective_from->format('d/m/Y')} ";
    echo "até " . ($assignment->effective_until ? $assignment->effective_until->format('d/m/Y') : 'atualmente');
    echo " - Status: {$assignment->status}\n";
}
```

---

## 🧪 Testando com Tinker

```bash
php artisan tinker
```

Dentro do Tinker, você pode executar todos os exemplos acima. Exemplo:

```php
// Listar presets
$templates = \App\Models\WorkShiftTemplate::presets()->get();
foreach ($templates as $t) {
    echo "{$t->id}: {$t->name}\n";
}

// Ver detalhes de um preset
$template = \App\Models\WorkShiftTemplate::with(['weeklySchedules', 'rotatingRule'])->find(1);
echo $template->name . " - " . $template->type_formatted . "\n";

if ($template->weeklySchedules->count() > 0) {
    foreach ($template->weeklySchedules as $schedule) {
        echo "  {$schedule->day_name}: ";
        if ($schedule->is_work_day) {
            echo "{$schedule->entry_1->format('H:i')} - {$schedule->exit_1->format('H:i')}";
            if ($schedule->entry_2) {
                echo " | {$schedule->entry_2->format('H:i')} - {$schedule->exit_2->format('H:i')}";
            }
            echo " ({$schedule->daily_hours}h)\n";
        } else {
            echo "Folga\n";
        }
    }
}
```

---

## 🎨 Próximos Passos para Completar a Implementação

### 1. Criar Controllers

```bash
php artisan make:controller WorkShiftTemplateController --resource
php artisan make:controller WorkShiftAssignmentController
```

### 2. Adicionar Rotas (routes/web.php)

```php
Route::prefix('work-shifts')->name('work-shifts.')->middleware(['auth'])->group(function () {
    // Templates
    Route::resource('templates', WorkShiftTemplateController::class);
    Route::post('templates/{id}/duplicate', [WorkShiftTemplateController::class, 'duplicate'])->name('templates.duplicate');
    Route::get('presets', [WorkShiftTemplateController::class, 'presets'])->name('presets');
    
    // Atribuições
    Route::get('assign', [WorkShiftAssignmentController::class, 'index'])->name('assign.index');
    Route::post('assign', [WorkShiftAssignmentController::class, 'assign'])->name('assign.store');
    Route::get('employees/{id}/history', [WorkShiftAssignmentController::class, 'history'])->name('history');
    Route::delete('unassign', [WorkShiftAssignmentController::class, 'bulk_unassign'])->name('unassign');
});
```

### 3. Criar Views

- `resources/views/work-shifts/templates/index.blade.php`
- `resources/views/work-shifts/templates/create.blade.php`
- `resources/views/work-shifts/templates/edit.blade.php`
- `resources/views/work-shifts/templates/show.blade.php`
- `resources/views/work-shifts/assign/index.blade.php`
- `resources/views/work-shifts/assign/history.blade.php`

### 4. Adicionar ao Menu Principal

No seu layout principal, adicione o link:

```html
<li class="nav-item">
    <a class="nav-link" href="{{ route('work-shifts.templates.index') }}">
        <i class="fas fa-clock"></i> Jornadas e Escalas
    </a>
</li>
```

---

## 📚 Documentação Adicional

- **Documentação Completa:** Ver arquivo `MODULO_JORNADAS_ESCALAS.md`
- **Arquitetura do Sistema:** Ver arquivo `SYSTEM_ARCHITECTURE.md` (se existir)
- **Guia de Testes:** Ver arquivo `GUIA_TESTES.md` (quando criado)

---

## 🆘 Suporte

Para dúvidas ou problemas:
1. Consulte a documentação completa em `MODULO_JORNADAS_ESCALAS.md`
2. Verifique os exemplos neste guia
3. Use `php artisan tinker` para testar a lógica

---

**Última atualização:** 30/10/2025  
**Versão:** 1.0
