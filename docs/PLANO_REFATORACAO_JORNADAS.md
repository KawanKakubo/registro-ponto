# 📋 PLANO DE REFATORAÇÃO - SISTEMA DE JORNADAS DE TRABALHO

**Data:** 31/10/2025  
**Objetivo:** Expandir o sistema para suportar 3 tipos distintos de jornadas de trabalho

---

## 🎯 VISÃO GERAL

### Situação Atual
- ✅ Estrutura de banco criada (`work_shift_templates`, `template_weekly_schedules`, `template_rotating_rules`)
- ✅ Sistema suporta 2 tipos: `weekly` e `rotating_shift`
- ❌ Interface mostra apenas formulário semanal fixo
- ❌ Tipo `rotating_shift` não está sendo usado corretamente (falta lógica de apuração)
- ❌ Não existe tipo "Carga Horária" para professores

### Situação Desejada
3 tipos de jornada totalmente funcionais:
1. **Jornada Semanal Fixa** - Para administrativo (já existe, precisa ajustar interface)
2. **Escala de Revezamento** - Para hospital/SAMU (existe estrutura, falta lógica)
3. **Carga Horária Semanal** - Para professores (precisa criar)

---

## 📊 PARTE 1: REFATORAÇÃO DO BANCO DE DADOS

### 1.1 Modificar `work_shift_templates` (Migration)

**Alterações necessárias:**
```sql
-- Adicionar novo tipo 'weekly_hours' ao ENUM
ALTER TYPE work_shift_templates_type 
ADD VALUE IF NOT EXISTS 'weekly_hours';

-- Adicionar colunas para configuração flexível
ALTER TABLE work_shift_templates
ADD COLUMN calculation_mode VARCHAR(20) DEFAULT 'fixed_schedule' 
    COMMENT 'fixed_schedule, rotating_cycle, flexible_hours';
```

**Novos valores de `type`:**
- `weekly` → Jornada Semanal Fixa
- `rotating_shift` → Escala de Revezamento  
- `weekly_hours` → Carga Horária Semanal (NOVO)

### 1.2 Criar nova tabela `template_flexible_hours` (NOVA)

Para armazenar configurações de carga horária flexível:

```php
Schema::create('template_flexible_hours', function (Blueprint $table) {
    $table->id();
    $table->foreignId('template_id')
        ->constrained('work_shift_templates')
        ->onDelete('cascade');
    
    $table->decimal('weekly_hours_required', 5, 2)
        ->comment('Carga horária semanal exigida (ex: 20h, 30h)');
    
    $table->enum('period_type', ['weekly', 'biweekly', 'monthly'])
        ->default('weekly')
        ->comment('Período de apuração');
    
    $table->integer('grace_minutes')
        ->default(0)
        ->comment('Tolerância em minutos para considerar falta');
    
    $table->boolean('requires_minimum_daily_hours')
        ->default(false)
        ->comment('Se exige mínimo de horas por dia trabalhado');
    
    $table->decimal('minimum_daily_hours', 4, 2)
        ->nullable()
        ->comment('Mínimo de horas por dia (se aplicável)');
    
    $table->timestamps();
    
    $table->unique('template_id');
});
```

### 1.3 Ajustar `template_rotating_rules`

Adicionar campos para melhor controle das escalas:

```php
Schema::table('template_rotating_rules', function (Blueprint $table) {
    $table->boolean('uses_cycle_pattern')->default(true)
        ->comment('Se usa padrão de ciclo (ex: 12x36, 24x72)');
    
    $table->integer('total_cycle_days')
        ->storedAs('work_days + rest_days')
        ->comment('Total de dias no ciclo completo');
    
    $table->boolean('validate_exact_hours')->default(true)
        ->comment('Se valida horas exatas ou apenas presença');
});
```

### 1.4 Adicionar coluna em `employee_work_shift_assignments`

Para armazenar configurações específicas do colaborador:

```php
Schema::table('employee_work_shift_assignments', function (Blueprint $table) {
    $table->json('custom_settings')->nullable()
        ->comment('Configurações personalizadas por colaborador');
    
    // Exemplo de JSON:
    // {
    //   "weekly_hours_override": 25,
    //   "working_days": [1,2,3,4,5],
    //   "cycle_reference_date": "2025-01-01"
    // }
});
```

---

## 🎨 PARTE 2: REFATORAÇÃO DA INTERFACE

### 2.1 Nova Tela: Seleção do Tipo de Jornada

**Arquivo:** `resources/views/work-shift-templates/select-type.blade.php` (NOVO)

**Fluxo:**
1. Usuário acessa "Criar Nova Jornada"
2. Sistema mostra 3 cards grandes com os tipos
3. Ao clicar em um card, redireciona para formulário específico

**Design:**
```
┌─────────────────────────────────────────────────────────────┐
│  Qual tipo de jornada deseja criar?                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │  📅 SEMANAL  │  │  🔄 ESCALA   │  │  ⏱️ CARGA H. │     │
│  │    FIXA      │  │  REVEZAMENTO │  │   SEMANAL    │     │
│  │              │  │              │  │              │     │
│  │ Horários     │  │ Plantões     │  │ Professores  │     │
│  │ fixos por    │  │ 12x36, 24x72 │  │ 20h, 30h     │     │
│  │ dia semana   │  │              │  │              │     │
│  │              │  │              │  │              │     │
│  │ [Selecionar] │  │ [Selecionar] │  │ [Selecionar] │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Formulário Tipo 1: Jornada Semanal Fixa

**Arquivo:** `resources/views/work-shift-templates/create-weekly.blade.php` (RENOMEAR atual)

**Mantém o formulário atual** da imagem `image_990aa1.png`:
- Nome do modelo
- Carga horária semanal
- Checkbox para cada dia da semana
- Campos de entrada/saída (E1, S1, E2, S2, E3, S3)

**Nenhuma alteração necessária no formulário existente.**

### 2.3 Formulário Tipo 2: Escala de Revezamento

**Arquivo:** `resources/views/work-shift-templates/create-rotating.blade.php` (NOVO)

**Campos do Formulário:**
```
┌─────────────────────────────────────────────────────────────┐
│  Criar Escala de Revezamento                                │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Nome do Modelo: [Plantão 12x36 - Hospital____________]     │
│                                                              │
│  Descrição: [Escala para enfermeiros e médicos______...]    │
│                                                              │
│  ┌─ Configuração do Ciclo ──────────────────────────────┐  │
│  │                                                       │  │
│  │  Dias de Trabalho: [1] dia(s) por ciclo             │  │
│  │  Dias de Descanso: [2] dia(s) por ciclo             │  │
│  │                                                       │  │
│  │  Ciclo Completo: 3 dias (1 trabalho + 2 descanso)   │  │
│  │                                                       │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌─ Horário do Plantão ──────────────────────────────────┐  │
│  │                                                       │  │
│  │  Horário de Início: [19:00]                          │  │
│  │  Horário de Término: [07:00] (dia seguinte)          │  │
│  │                                                       │  │
│  │  Duração Calculada: 12 horas                         │  │
│  │                                                       │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌─ Regras de Validação ─────────────────────────────────┐  │
│  │                                                       │  │
│  │  ☑ Validar horário exato de entrada/saída            │  │
│  │  ☑ Permitir tolerância de: [15] minutos              │  │
│  │  ☐ Permitir apenas marcação de presença (sem horário)│  │
│  │                                                       │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  [Cancelar]  [Criar Modelo de Escala]                       │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

**Exemplos de Escalas Comuns:**
- 12x36: `work_days=1, rest_days=2` (ciclo 3 dias)
- 24x72: `work_days=1, rest_days=3` (ciclo 4 dias)
- 24x48: `work_days=1, rest_days=2` (ciclo 3 dias)

### 2.4 Formulário Tipo 3: Carga Horária Semanal

**Arquivo:** `resources/views/work-shift-templates/create-flexible.blade.php` (NOVO)

**Campos do Formulário:**
```
┌─────────────────────────────────────────────────────────────┐
│  Criar Jornada por Carga Horária                            │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Nome do Modelo: [Professor 20h___________________]         │
│                                                              │
│  Descrição: [Carga horária flexível para docentes...]       │
│                                                              │
│  ┌─ Configuração da Carga Horária ──────────────────────┐  │
│  │                                                       │  │
│  │  Carga Horária Semanal: [20] horas por semana        │  │
│  │                                                       │  │
│  │  Período de Apuração:                                │  │
│  │    ⦿ Semanal (segunda a domingo)                     │  │
│  │    ○ Quinzenal                                       │  │
│  │    ○ Mensal                                          │  │
│  │                                                       │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌─ Regras de Controle (Opcional) ──────────────────────┐  │
│  │                                                       │  │
│  │  ☐ Exigir mínimo de horas por dia trabalhado         │  │
│  │    └─ Mínimo: [__] horas por dia                    │  │
│  │                                                       │  │
│  │  ☐ Exigir mínimo de dias por semana                  │  │
│  │    └─ Mínimo: [__] dias por semana                  │  │
│  │                                                       │  │
│  │  Tolerância para falta: [15] minutos                 │  │
│  │    (Se trabalhar menos que isso, considera ausência) │  │
│  │                                                       │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  ⓘ Neste modelo, o sistema somará todas as horas           │
│     trabalhadas no período e comparará com a carga          │
│     horária devida. Horários fixos não são validados.       │
│                                                              │
│  [Cancelar]  [Criar Modelo de Carga Horária]                │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

**Exemplos de Uso:**
- Professor 20h: `weekly_hours_required = 20`
- Professor 30h: `weekly_hours_required = 30`
- Professor 40h: `weekly_hours_required = 40`

---

## 🔧 PARTE 3: LÓGICA DE APURAÇÃO DE PONTO

### 3.1 Tipo 1: Jornada Semanal Fixa (JÁ EXISTE)

**Lógica Atual:**
```
Para cada dia:
  1. Buscar WorkSchedule do colaborador para day_of_week
  2. Se não houver, considerar folga
  3. Comparar batidas com horários esperados (entry_1, exit_1, etc)
  4. Calcular atrasos, saídas antecipadas, horas extras
  5. Somar total do dia
```

**Status:** ✅ Funcionando - Não precisa alterar

### 3.2 Tipo 2: Escala de Revezamento (NOVO)

**Serviço:** `app/Services/RotatingShiftCalculationService.php` (CRIAR)

**Pseudocódigo:**
```php
class RotatingShiftCalculationService
{
    /**
     * Calcula se o colaborador deveria trabalhar em uma data específica
     * baseado no ciclo de revezamento
     */
    public function shouldWorkOnDate(
        Employee $employee,
        Carbon $date,
        TemplateRotatingRule $rule
    ): bool {
        // 1. Pegar a data de início do ciclo do colaborador
        $assignment = $employee->workShiftAssignments()
            ->where('effective_from', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', $date);
            })
            ->first();
        
        if (!$assignment || !$assignment->cycle_start_date) {
            throw new Exception('Colaborador sem data de início de ciclo');
        }
        
        $cycleStartDate = Carbon::parse($assignment->cycle_start_date);
        
        // 2. Calcular quantos dias se passaram desde o início do ciclo
        $daysSinceStart = $cycleStartDate->diffInDays($date);
        
        // 3. Determinar a posição no ciclo atual
        $totalCycleDays = $rule->work_days + $rule->rest_days;
        $positionInCycle = $daysSinceStart % $totalCycleDays;
        
        // 4. Verificar se está em dia de trabalho
        // Exemplo: 12x36 (1 trabalho, 2 descanso)
        // Posição 0 = trabalho
        // Posição 1 = descanso
        // Posição 2 = descanso
        // Posição 3 = trabalho novamente...
        
        $isWorkDay = $positionInCycle < $rule->work_days;
        
        return $isWorkDay;
    }
    
    /**
     * Valida as batidas de ponto para escala rotativa
     */
    public function validateAttendance(
        Employee $employee,
        Carbon $date,
        array $clockIns,
        TemplateRotatingRule $rule
    ): array {
        // Verificar se deveria trabalhar neste dia
        $shouldWork = $this->shouldWorkOnDate($employee, $date, $rule);
        
        if (!$shouldWork) {
            // Não era dia de trabalho
            return [
                'should_work' => false,
                'status' => 'rest_day',
                'hours_worked' => 0,
                'hours_expected' => 0,
            ];
        }
        
        // Era dia de trabalho, validar horários
        if (empty($clockIns)) {
            // Falta
            return [
                'should_work' => true,
                'status' => 'absent',
                'hours_worked' => 0,
                'hours_expected' => $rule->shift_duration_hours,
                'hours_missing' => $rule->shift_duration_hours,
            ];
        }
        
        // Calcular horas trabalhadas
        $hoursWorked = $this->calculateHoursFromClockIns($clockIns);
        $expectedHours = $rule->shift_duration_hours;
        
        // Verificar se cumpriu a jornada
        $tolerance = 0.25; // 15 minutos
        $difference = $expectedHours - $hoursWorked;
        
        if (abs($difference) <= $tolerance) {
            $status = 'complete';
        } elseif ($hoursWorked < $expectedHours) {
            $status = 'incomplete';
        } else {
            $status = 'overtime';
        }
        
        return [
            'should_work' => true,
            'status' => $status,
            'hours_worked' => $hoursWorked,
            'hours_expected' => $expectedHours,
            'difference' => $difference,
            'shift_start' => $rule->shift_start_time,
            'shift_end' => $rule->shift_end_time,
        ];
    }
    
    /**
     * Gera o calendário de trabalho para um período
     */
    public function generateWorkCalendar(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
        TemplateRotatingRule $rule
    ): array {
        $calendar = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $calendar[] = [
                'date' => $currentDate->format('Y-m-d'),
                'should_work' => $this->shouldWorkOnDate(
                    $employee, 
                    $currentDate, 
                    $rule
                ),
            ];
            
            $currentDate->addDay();
        }
        
        return $calendar;
    }
}
```

**Exemplo Prático - Escala 12x36:**
```
Regra: work_days=1, rest_days=2 (ciclo de 3 dias)
Colaborador A: cycle_start_date = 2025-01-01 (trabalha)
Colaborador B: cycle_start_date = 2025-01-02 (trabalha)
Colaborador C: cycle_start_date = 2025-01-03 (trabalha)

Calendário gerado:
Data       | Colab A | Colab B | Colab C
-----------|---------|---------|--------
2025-01-01 | TRAB    | FOLGA   | FOLGA
2025-01-02 | FOLGA   | TRAB    | FOLGA
2025-01-03 | FOLGA   | FOLGA   | TRAB
2025-01-04 | TRAB    | FOLGA   | FOLGA  (ciclo reinicia)
2025-01-05 | FOLGA   | TRAB    | FOLGA
```

### 3.3 Tipo 3: Carga Horária Semanal (NOVO)

**Serviço:** `app/Services/FlexibleHoursCalculationService.php` (CRIAR)

**Pseudocódigo:**
```php
class FlexibleHoursCalculationService
{
    /**
     * Calcula as horas trabalhadas em um período
     * e compara com a carga horária devida
     */
    public function calculatePeriodBalance(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
        TemplateFlexibleHours $config
    ): array {
        // 1. Buscar todas as batidas do período
        $attendances = $employee->attendances()
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // 2. Calcular total de horas trabalhadas
        $totalHoursWorked = 0;
        $workingDays = [];
        
        foreach ($attendances as $attendance) {
            $dailyHours = $this->calculateDailyHours($attendance);
            
            // Verificar tolerância mínima
            if ($dailyHours >= ($config->grace_minutes / 60)) {
                $totalHoursWorked += $dailyHours;
                $workingDays[] = $attendance->date;
            }
        }
        
        // 3. Calcular horas devidas no período
        $periodType = $config->period_type;
        $hoursRequired = $this->calculateRequiredHours(
            $periodType, 
            $config->weekly_hours_required,
            $startDate,
            $endDate
        );
        
        // 4. Calcular diferença
        $balance = $totalHoursWorked - $hoursRequired;
        
        // 5. Determinar status
        $tolerance = $config->grace_minutes / 60;
        
        if (abs($balance) <= $tolerance) {
            $status = 'complete';
        } elseif ($balance < 0) {
            $status = 'insufficient';
        } else {
            $status = 'overtime';
        }
        
        // 6. Validar regras opcionais
        $violations = [];
        
        if ($config->requires_minimum_daily_hours) {
            $violations = $this->validateMinimumDailyHours(
                $attendances,
                $config->minimum_daily_hours
            );
        }
        
        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'type' => $periodType,
            ],
            'hours' => [
                'required' => $hoursRequired,
                'worked' => round($totalHoursWorked, 2),
                'balance' => round($balance, 2),
            ],
            'days' => [
                'worked' => count($workingDays),
                'dates' => $workingDays,
            ],
            'status' => $status,
            'violations' => $violations,
        ];
    }
    
    /**
     * Calcula horas devidas baseado no tipo de período
     */
    private function calculateRequiredHours(
        string $periodType,
        float $weeklyHours,
        Carbon $start,
        Carbon $end
    ): float {
        switch ($periodType) {
            case 'weekly':
                // Uma semana = carga horária configurada
                return $weeklyHours;
            
            case 'biweekly':
                // Duas semanas = 2x carga horária
                return $weeklyHours * 2;
            
            case 'monthly':
                // Calcular semanas no mês
                $weeks = $start->diffInWeeks($end);
                return $weeklyHours * $weeks;
            
            default:
                return $weeklyHours;
        }
    }
    
    /**
     * Calcula horas trabalhadas em um dia
     */
    private function calculateDailyHours(Attendance $attendance): float
    {
        $totalMinutes = 0;
        
        // Somar todos os períodos
        if ($attendance->entry_1 && $attendance->exit_1) {
            $totalMinutes += $this->minutesBetween(
                $attendance->entry_1,
                $attendance->exit_1
            );
        }
        
        if ($attendance->entry_2 && $attendance->exit_2) {
            $totalMinutes += $this->minutesBetween(
                $attendance->entry_2,
                $attendance->exit_2
            );
        }
        
        if ($attendance->entry_3 && $attendance->exit_3) {
            $totalMinutes += $this->minutesBetween(
                $attendance->entry_3,
                $attendance->exit_3
            );
        }
        
        if ($attendance->entry_4 && $attendance->exit_4) {
            $totalMinutes += $this->minutesBetween(
                $attendance->entry_4,
                $attendance->exit_4
            );
        }
        
        return $totalMinutes / 60;
    }
    
    /**
     * Gera relatório semanal para professor
     */
    public function generateWeeklyReport(
        Employee $employee,
        Carbon $weekStart
    ): array {
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $config = $employee->activeWorkShiftAssignment
            ->template
            ->flexibleHoursConfig;
        
        $balance = $this->calculatePeriodBalance(
            $employee,
            $weekStart,
            $weekEnd,
            $config
        );
        
        // Buscar batidas detalhadas de cada dia
        $dailyDetails = [];
        $currentDate = $weekStart->copy();
        
        while ($currentDate->lte($weekEnd)) {
            $attendance = $employee->attendances()
                ->where('date', $currentDate->format('Y-m-d'))
                ->first();
            
            $dailyDetails[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->dayName,
                'worked' => $attendance ? true : false,
                'hours' => $attendance 
                    ? $this->calculateDailyHours($attendance) 
                    : 0,
                'entries' => $attendance ? [
                    'entry_1' => $attendance->entry_1,
                    'exit_1' => $attendance->exit_1,
                    'entry_2' => $attendance->entry_2,
                    'exit_2' => $attendance->exit_2,
                ] : null,
            ];
            
            $currentDate->addDay();
        }
        
        return [
            'summary' => $balance,
            'daily_breakdown' => $dailyDetails,
        ];
    }
}
```

**Exemplo Prático - Professor 20h:**
```
Semana: 27/10 a 02/11/2025
Carga devida: 20 horas

Segunda   27/10: 08:00-12:00            = 4h
Terça     28/10: 13:00-18:00            = 5h
Quarta    29/10: 08:00-13:00            = 5h
Quinta    30/10: Não trabalhou          = 0h
Sexta     31/10: 14:00-20:00            = 6h
Sábado    01/11: Não trabalhou          = 0h
Domingo   02/11: Não trabalhou          = 0h

TOTAL TRABALHADO: 20h
CARGA DEVIDA: 20h
SALDO: 0h ✅ COMPLETO
```

---

## 🚀 PARTE 4: PLANO DE IMPLEMENTAÇÃO

### Fase 1: Banco de Dados (1-2 horas)
1. ✅ Criar migration para adicionar tipo `weekly_hours`
2. ✅ Criar tabela `template_flexible_hours`
3. ✅ Adicionar colunas extras em `template_rotating_rules`
4. ✅ Adicionar `custom_settings` JSON em `employee_work_shift_assignments`
5. ✅ Rodar migrations

### Fase 2: Models (30 min)
1. ✅ Criar model `TemplateFlexibleHours`
2. ✅ Atualizar relacionamentos em `WorkShiftTemplate`
3. ✅ Adicionar casts e accessors

### Fase 3: Services (3-4 horas)
1. ✅ Criar `RotatingShiftCalculationService`
2. ✅ Criar `FlexibleHoursCalculationService`
3. ✅ Atualizar `WorkShiftTemplateService` para suportar novos tipos
4. ✅ Criar testes unitários básicos

### Fase 4: Interface (2-3 horas)
1. ✅ Criar `select-type.blade.php`
2. ✅ Renomear `create.blade.php` → `create-weekly.blade.php`
3. ✅ Criar `create-rotating.blade.php`
4. ✅ Criar `create-flexible.blade.php`
5. ✅ Atualizar rotas no controller

### Fase 5: Controller (1 hora)
1. ✅ Atualizar `WorkShiftTemplateController@create` para redirecionar
2. ✅ Criar métodos específicos: `createWeekly`, `createRotating`, `createFlexible`
3. ✅ Atualizar `store` para lidar com 3 tipos

### Fase 6: Apuração de Ponto (2-3 horas)
1. ✅ Atualizar serviço de cálculo de ponto existente
2. ✅ Integrar `RotatingShiftCalculationService`
3. ✅ Integrar `FlexibleHoursCalculationService`
4. ✅ Criar views de relatório específicas para cada tipo

### Fase 7: Testes e Ajustes (2 horas)
1. ✅ Testar criação de cada tipo de jornada
2. ✅ Testar aplicação em colaboradores
3. ✅ Testar cálculo de ponto para cada tipo
4. ✅ Ajustar edge cases

**TEMPO TOTAL ESTIMADO: 12-16 horas**

---

## 📝 CHECKLIST DE ENTREGA

### Banco de Dados
- [ ] Migration para novo tipo `weekly_hours`
- [ ] Tabela `template_flexible_hours` criada
- [ ] Colunas extras em `template_rotating_rules`
- [ ] Campo `custom_settings` em assignments

### Models
- [ ] Model `TemplateFlexibleHours`
- [ ] Relacionamentos atualizados
- [ ] Casts configurados

### Services
- [ ] `RotatingShiftCalculationService` completo
- [ ] `FlexibleHoursCalculationService` completo
- [ ] Integração com sistema de ponto

### Interface
- [ ] Tela de seleção de tipo
- [ ] Formulário semanal (já existe)
- [ ] Formulário escala rotativa
- [ ] Formulário carga horária

### Funcionalidades
- [ ] Criar jornada semanal (já funciona)
- [ ] Criar escala rotativa
- [ ] Criar carga horária
- [ ] Aplicar em colaboradores
- [ ] Calcular ponto tipo 1 (já funciona)
- [ ] Calcular ponto tipo 2
- [ ] Calcular ponto tipo 3
- [ ] Gerar relatórios específicos

### Documentação
- [ ] README atualizado
- [ ] Exemplos de uso
- [ ] Diagramas de fluxo

---

## 🎓 EXEMPLOS DE USO REAL

### Exemplo 1: Administrativo (Tipo 1)
```
Modelo: "Comercial Padrão 40h"
Tipo: weekly
Horários:
  Seg-Sex: 08:00-12:00 / 13:00-17:00 (8h/dia)
  Sáb-Dom: Folga

Total: 40h/semana
```

### Exemplo 2: Hospital 12x36 (Tipo 2)
```
Modelo: "Enfermeiros 12x36"
Tipo: rotating_shift
Configuração:
  work_days: 1
  rest_days: 2
  shift_start_time: 19:00
  shift_end_time: 07:00 (próximo dia)
  shift_duration_hours: 12

3 colaboradores em revezamento:
  - Colaborador A: cycle_start = 01/11 (trabalha dias 01, 04, 07...)
  - Colaborador B: cycle_start = 02/11 (trabalha dias 02, 05, 08...)
  - Colaborador C: cycle_start = 03/11 (trabalha dias 03, 06, 09...)
```

### Exemplo 3: Professor 20h (Tipo 3)
```
Modelo: "Professor 20h"
Tipo: weekly_hours
Configuração:
  weekly_hours_required: 20
  period_type: weekly
  grace_minutes: 15
  requires_minimum_daily_hours: false

Apuração: Soma todas as horas da semana
Validação: Se >= 20h → OK | Se < 20h → Falta
```

---

## 🔍 DIAGRAMAS

### Fluxo de Criação de Jornada
```
                    ┌─────────────────┐
                    │ Usuário clica   │
                    │ "Nova Jornada"  │
                    └────────┬────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │ Tela: Selecione │
                    │ o tipo          │
                    └────┬───┬───┬────┘
                         │   │   │
          ┌──────────────┘   │   └──────────────┐
          ▼                  ▼                   ▼
    ┌──────────┐      ┌──────────┐       ┌──────────┐
    │ Semanal  │      │  Escala  │       │  Carga   │
    │  Fixa    │      │ Rotatória│       │  Horária │
    └────┬─────┘      └────┬─────┘       └────┬─────┘
         │                 │                    │
         ▼                 ▼                    ▼
    Form Atual         Form Novo            Form Novo
    (já existe)        (12x36)              (20h/30h)
         │                 │                    │
         └─────────────────┴────────────────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │ Salvar no   │
                    │ Banco       │
                    └──────┬──────┘
                           │
                           ▼
                    ┌─────────────┐
                    │ Aplicar em  │
                    │Colaboradores│
                    └─────────────┘
```

### Fluxo de Apuração de Ponto
```
                    ┌──────────────┐
                    │ Batida de    │
                    │ Ponto        │
                    └──────┬───────┘
                           │
                           ▼
                    ┌──────────────┐
                    │ Buscar tipo  │
                    │ de jornada   │
                    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              │            │            │
              ▼            ▼            ▼
         ┌────────┐   ┌────────┐   ┌────────┐
         │ Tipo 1 │   │ Tipo 2 │   │ Tipo 3 │
         │Semanal │   │ Escala │   │ Carga  │
         └───┬────┘   └───┬────┘   └───┬────┘
             │            │            │
             ▼            ▼            ▼
      Validar com    Calcular       Somar
      horários      posição no     horas do
      fixos         ciclo          período
             │            │            │
             └────────────┼────────────┘
                          │
                          ▼
                   ┌──────────────┐
                   │ Gerar saldo  │
                   │ do dia/semana│
                   └──────────────┘
```

---

## 📞 PRÓXIMOS PASSOS

1. **Aprovação deste plano** pelo solicitante
2. **Início da implementação** seguindo as fases
3. **Testes incrementais** após cada fase
4. **Validação com usuários** finais (RH)
5. **Ajustes finos** baseados no feedback
6. **Documentação** de uso para operadores
7. **Treinamento** da equipe

---

**Documento criado em:** 31/10/2025  
**Autor:** GitHub Copilot  
**Status:** Aguardando aprovação
