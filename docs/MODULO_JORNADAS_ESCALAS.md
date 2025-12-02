# 📘 Módulo de Jornadas e Escalas de Trabalho

## 🎯 Objetivo

Criar um sistema de gerenciamento de jornadas baseado em **templates/modelos**, permitindo que gestores configurem horários de trabalho para centenas de colaboradores em minutos, substituindo o método manual e individual atual.

---

## 🗂️ 1. Esquema do Banco de Dados

### 1.1 Tabela: `work_shift_templates` (Modelos de Jornada)

Armazena os modelos/templates de jornadas que podem ser reutilizados.

```sql
CREATE TABLE work_shift_templates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    type ENUM('weekly', 'rotating_shift') NOT NULL,
    is_preset BOOLEAN DEFAULT FALSE,
    weekly_hours DECIMAL(5,2) NULL COMMENT 'Total de horas semanais (ex: 44.00)',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_is_preset (is_preset)
);
```

**Campos:**
- `name`: Nome descritivo (ex: "Administrativo Padrão", "Escala 12x36")
- `type`: Tipo da jornada
  - `weekly`: Jornada semanal padrão (horários fixos por dia da semana)
  - `rotating_shift`: Escala de revezamento (12x36, 6x1, etc.)
- `is_preset`: Indica se é um preset do sistema (não pode ser deletado)
- `weekly_hours`: Total de horas trabalhadas por semana

---

### 1.2 Tabela: `template_weekly_schedules` (Horários Semanais do Template)

Define os horários para cada dia da semana em templates do tipo `weekly`.

```sql
CREATE TABLE template_weekly_schedules (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    template_id BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '0=Domingo, 1=Segunda, ..., 6=Sábado',
    entry_1 TIME NULL,
    exit_1 TIME NULL,
    entry_2 TIME NULL,
    exit_2 TIME NULL,
    entry_3 TIME NULL,
    exit_3 TIME NULL,
    is_work_day BOOLEAN DEFAULT TRUE,
    daily_hours DECIMAL(4,2) NULL COMMENT 'Total de horas no dia',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (template_id) REFERENCES work_shift_templates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_template_day (template_id, day_of_week),
    INDEX idx_template_day (template_id, day_of_week)
);
```

**Campos:**
- `is_work_day`: Se FALSE, o dia é folga (entry/exit serão NULL)
- `daily_hours`: Total de horas trabalhadas no dia (calculado automaticamente)

---

### 1.3 Tabela: `template_rotating_rules` (Regras de Escalas Rotativas)

Define as regras para templates do tipo `rotating_shift`.

```sql
CREATE TABLE template_rotating_rules (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    template_id BIGINT UNSIGNED NOT NULL,
    work_days INT NOT NULL COMMENT 'Dias de trabalho no ciclo (ex: 12 para 12x36, 6 para 6x1)',
    rest_days INT NOT NULL COMMENT 'Dias de descanso no ciclo (ex: 36 para 12x36, 1 para 6x1)',
    shift_start_time TIME NULL COMMENT 'Horário de início do turno',
    shift_end_time TIME NULL COMMENT 'Horário de fim do turno',
    shift_duration_hours DECIMAL(4,2) NULL COMMENT 'Duração do turno em horas',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (template_id) REFERENCES work_shift_templates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_template (template_id)
);
```

**Exemplo de registros:**
- Escala 12x36: `work_days=1, rest_days=1, shift_duration_hours=12.00`
- Escala 6x1: `work_days=6, rest_days=1, shift_duration_hours=8.00`

---

### 1.4 Tabela: `employee_work_shift_assignments` (Atribuições de Jornadas)

Associa colaboradores aos templates de jornada.

```sql
CREATE TABLE employee_work_shift_assignments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED NOT NULL,
    cycle_start_date DATE NULL COMMENT 'Data de início do ciclo (obrigatório para rotating_shift)',
    effective_from DATE NOT NULL COMMENT 'Data a partir da qual a jornada é válida',
    effective_until DATE NULL COMMENT 'Data até a qual a jornada é válida (NULL = sem fim)',
    assigned_by BIGINT UNSIGNED NULL,
    assigned_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES work_shift_templates(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_employee_effective (employee_id, effective_from, effective_until),
    INDEX idx_template (template_id),
    INDEX idx_dates (effective_from, effective_until)
);
```

**Lógica de vigência:**
- `effective_from`: Data inicial da atribuição
- `effective_until`: Data final (NULL = vigência indefinida)
- `cycle_start_date`: Para escalas rotativas, marca o início do ciclo individual do colaborador

---

### 1.5 Alteração na Tabela Existente: `work_schedules`

A tabela atual `work_schedules` **não será deletada**, mas será **depreciada gradualmente**. Adicionaremos um campo para indicar que o horário vem de um template:

```sql
ALTER TABLE work_schedules ADD COLUMN source_template_id BIGINT UNSIGNED NULL AFTER employee_id;
ALTER TABLE work_schedules ADD FOREIGN KEY (source_template_id) REFERENCES work_shift_templates(id) ON DELETE SET NULL;
```

**Estratégia de migração:**
1. Novos colaboradores usarão apenas templates
2. Colaboradores existentes podem continuar com horários manuais OU serem migrados para templates
3. O campo `source_template_id` indica se o horário foi gerado por um template

---

## 🧮 2. Lógica de Negócio

### 2.1 Cálculo de Escalas Rotativas (12x36, 6x1, etc.)

#### Algoritmo para determinar se um colaborador trabalha em uma data específica:

```php
function isWorkingDay(
    DateTime $targetDate, 
    DateTime $cycleStartDate, 
    int $workDays, 
    int $restDays
): bool {
    // Calcula quantos dias se passaram desde o início do ciclo
    $daysSinceStart = $targetDate->diff($cycleStartDate)->days;
    
    // Tamanho do ciclo completo
    $cycleLength = $workDays + $restDays;
    
    // Posição no ciclo atual (0 a cycleLength-1)
    $positionInCycle = $daysSinceStart % $cycleLength;
    
    // Se a posição é menor que workDays, é dia de trabalho
    return $positionInCycle < $workDays;
}
```

#### Exemplo prático - Escala 12x36:
- `cycleStartDate`: 2025-01-01
- `workDays`: 1
- `restDays`: 1
- `cycleLength`: 2

| Data       | Days Since Start | Position in Cycle | Trabalha? |
|------------|------------------|-------------------|-----------|
| 2025-01-01 | 0                | 0                 | ✅ Sim    |
| 2025-01-02 | 1                | 1                 | ❌ Não    |
| 2025-01-03 | 2                | 0                 | ✅ Sim    |
| 2025-01-04 | 3                | 1                 | ❌ Não    |

#### Exemplo prático - Escala 6x1:
- `cycleStartDate`: 2025-01-01
- `workDays`: 6
- `restDays`: 1
- `cycleLength`: 7

| Data       | Days Since Start | Position in Cycle | Trabalha? |
|------------|------------------|-------------------|-----------|
| 2025-01-01 | 0                | 0                 | ✅ Sim    |
| 2025-01-02 | 1                | 1                 | ✅ Sim    |
| ...        | ...              | ...               | ...       |
| 2025-01-06 | 5                | 5                 | ✅ Sim    |
| 2025-01-07 | 6                | 6                 | ❌ Não    |
| 2025-01-08 | 7                | 0                 | ✅ Sim    |

---

### 2.2 Geração Automática de Horários (Opcional)

Quando um template é atribuído a um colaborador, o sistema **pode** (mas não necessariamente) popular a tabela `work_schedules` com base no template. Isso facilita consultas rápidas sem recalcular sempre.

#### Processo:
1. Ao atribuir um template semanal (`weekly`):
   - Copia os registros de `template_weekly_schedules` para `work_schedules`
   - Define `effective_from` e `effective_until`
   
2. Ao atribuir um template rotativo (`rotating_shift`):
   - Calcula os próximos 90-180 dias
   - Cria registros em `work_schedules` para dias de trabalho
   - Atualiza periodicamente (job agendado)

---

## 🎨 3. Interface de Usuário (Mockups/Wireframes)

### 3.1 Tela: Listagem de Modelos de Jornada

```
╔════════════════════════════════════════════════════════════════╗
║  Modelos de Jornada                    [+ Novo Modelo]         ║
╠════════════════════════════════════════════════════════════════╣
║  🔍 Buscar: [____________]  Filtros: [Todos ▼] [Weekly ▼]     ║
╠════════════════════════════════════════════════════════════════╣
║  Modelo                     | Tipo     | Colaboradores | Ações ║
╟────────────────────────────────────────────────────────────────╢
║  📋 Administrativo Padrão   | Semanal  |     245      | ✏️ 👁️ ║
║  🏭 Turno Manhã - Fábrica   | Semanal  |      87      | ✏️ 👁️ ║
║  🌙 Escala 12x36 Noturno    | Rotativa |      34      | ✏️ 👁️ ║
║  ⚙️ Comercial 44h           | Semanal  |     156      | ✏️ 👁️ ║
║  🔄 Escala 6x1              | Rotativa |      78      | ✏️ 👁️ ║
╚════════════════════════════════════════════════════════════════╝
```

---

### 3.2 Tela: Criar/Editar Modelo de Jornada

#### Passo 1: Escolha o Tipo

```
╔════════════════════════════════════════════════════════════════╗
║  Criar Novo Modelo de Jornada                                  ║
╠════════════════════════════════════════════════════════════════╣
║                                                                 ║
║  Escolha como deseja começar:                                  ║
║                                                                 ║
║  ┌──────────────────────┐  ┌──────────────────────┐          ║
║  │  📋 Criar do Zero    │  │  ⚡ Usar um Preset   │          ║
║  │                      │  │                      │          ║
║  │  Configure todos os  │  │  Escolha um modelo   │          ║
║  │  detalhes manualmente│  │  pré-configurado     │          ║
║  │                      │  │                      │          ║
║  │    [Selecionar]      │  │    [Selecionar]      │          ║
║  └──────────────────────┘  └──────────────────────┘          ║
║                                                                 ║
╚════════════════════════════════════════════════════════════════╝
```

#### Passo 2: Selecionar Preset (se escolhido)

```
╔════════════════════════════════════════════════════════════════╗
║  Escolha um Preset                                   [← Voltar]║
╠════════════════════════════════════════════════════════════════╣
║                                                                 ║
║  ⚙️ Comercial (44h/semana)                                     ║
║     Segunda a Sexta: 08:00-12:00 | 13:00-18:00                ║
║     Sábado: 08:00-12:00  |  Domingo: Folga                    ║
║     [Usar este preset]                                         ║
║  ────────────────────────────────────────────────────────────  ║
║  💼 Administrativo (40h/semana)                                ║
║     Segunda a Sexta: 08:00-12:00 | 13:00-17:00                ║
║     Sábado e Domingo: Folga                                    ║
║     [Usar este preset]                                         ║
║  ────────────────────────────────────────────────────────────  ║
║  🌙 Escala 12x36                                               ║
║     12 horas de trabalho por 36 horas de descanso             ║
║     Turno: 19:00 às 07:00                                     ║
║     [Usar este preset]                                         ║
║  ────────────────────────────────────────────────────────────  ║
║  🔄 Escala 6x1                                                 ║
║     6 dias de trabalho por 1 de folga                         ║
║     Turno: 08:00 às 17:00 (8h/dia)                           ║
║     [Usar este preset]                                         ║
║                                                                 ║
╚════════════════════════════════════════════════════════════════╝
```

#### Passo 3: Configurar o Modelo (Tipo Semanal)

```
╔════════════════════════════════════════════════════════════════╗
║  Configurar Modelo: Jornada Semanal                 [← Voltar] ║
╠════════════════════════════════════════════════════════════════╣
║                                                                 ║
║  Nome do Modelo: [Administrativo Padrão_______________]        ║
║  Descrição: [Jornada padrão do setor administrativo____]       ║
║                                                                 ║
║  Carga Horária Semanal: [40] horas                            ║
║                                                                 ║
║  ┌────────────────────────────────────────────────────────┐   ║
║  │  Horários por Dia da Semana                            │   ║
║  ├────────────────────────────────────────────────────────┤   ║
║  │  Segunda-feira    ☑️ Dia de Trabalho                   │   ║
║  │    Entrada 1: [08:00]  Saída 1: [12:00]               │   ║
║  │    Entrada 2: [13:00]  Saída 2: [17:00]               │   ║
║  │    Total: 8h                                           │   ║
║  ├────────────────────────────────────────────────────────┤   ║
║  │  Terça-feira      ☑️ Dia de Trabalho                   │   ║
║  │    Entrada 1: [08:00]  Saída 1: [12:00]               │   ║
║  │    Entrada 2: [13:00]  Saída 2: [17:00]               │   ║
║  │    Total: 8h                                           │   ║
║  ├────────────────────────────────────────────────────────┤   ║
║  │  ... (idem para Quarta, Quinta, Sexta)                │   ║
║  ├────────────────────────────────────────────────────────┤   ║
║  │  Sábado          ☐ Dia de Folga                        │   ║
║  ├────────────────────────────────────────────────────────┤   ║
║  │  Domingo         ☐ Dia de Folga                        │   ║
║  └────────────────────────────────────────────────────────┘   ║
║                                                                 ║
║  [Cancelar]                           [Salvar Modelo]          ║
║                                                                 ║
╚════════════════════════════════════════════════════════════════╝
```

#### Passo 3: Configurar o Modelo (Tipo Escala Rotativa)

```
╔════════════════════════════════════════════════════════════════╗
║  Configurar Modelo: Escala Rotativa                 [← Voltar] ║
╠════════════════════════════════════════════════════════════════╣
║                                                                 ║
║  Nome do Modelo: [Escala 12x36 Noturno________________]        ║
║  Descrição: [Escala de 12h de trabalho por 36h de descanso]   ║
║                                                                 ║
║  ┌────────────────────────────────────────────────────────┐   ║
║  │  Configuração da Escala                                │   ║
║  ├────────────────────────────────────────────────────────┤   ║
║  │  Tipo de Escala: [12x36 ▼]                            │   ║
║  │                   • 12x36                              │   ║
║  │                   • 6x1                                │   ║
║  │                   • 4x2                                │   ║
║  │                   • Personalizado                      │   ║
║  │                                                        │   ║
║  │  Dias de Trabalho: [1] dias                           │   ║
║  │  Dias de Descanso: [1] dias                           │   ║
║  │                                                        │   ║
║  │  Horário do Turno:                                     │   ║
║  │    Início: [19:00]                                     │   ║
║  │    Fim:    [07:00] (dia seguinte)                     │   ║
║  │    Duração: 12 horas                                   │   ║
║  │                                                        │   ║
║  │  💡 O sistema calculará automaticamente os dias de     │   ║
║  │     trabalho e folga com base na data de início do     │   ║
║  │     ciclo de cada colaborador.                         │   ║
║  └────────────────────────────────────────────────────────┘   ║
║                                                                 ║
║  [Cancelar]                           [Salvar Modelo]          ║
║                                                                 ║
╚════════════════════════════════════════════════════════════════╝
```

---

### 3.3 Tela: Atribuição em Massa de Jornadas

Esta é a tela **mais importante** do sistema.

```
╔════════════════════════════════════════════════════════════════════════════════╗
║  Atribuir Jornadas de Trabalho                                                 ║
╠════════════════════════════════════════════════════════════════════════════════╣
║                                                                                 ║
║  Passo 1: Selecione o Modelo de Jornada                                       ║
║  ┌────────────────────────────────────────────────────────────────────────┐   ║
║  │  Modelo: [Administrativo Padrão ▼]                                      │   ║
║  │                                                                          │   ║
║  │  📋 Administrativo Padrão                                               │   ║
║  │  └─ Tipo: Semanal | 40h/semana                                         │   ║
║  │  └─ Seg-Sex: 08:00-12:00 | 13:00-17:00                                 │   ║
║  └────────────────────────────────────────────────────────────────────────┘   ║
║                                                                                 ║
║  Passo 2: Configure as Datas                                                   ║
║  ┌────────────────────────────────────────────────────────────────────────┐   ║
║  │  Vigência a partir de: [01/11/2025_____]                                │   ║
║  │  Vigência até: [________________] (deixe em branco para sem fim)        │   ║
║  │                                                                          │   ║
║  │  ⚠️ Para escalas rotativas:                                             │   ║
║  │  Data de início do ciclo: [01/11/2025_____]                             │   ║
║  │  (Define o primeiro dia de trabalho de cada colaborador)                │   ║
║  └────────────────────────────────────────────────────────────────────────┘   ║
║                                                                                 ║
║  Passo 3: Selecione os Colaboradores                                           ║
║  ┌────────────────────────────────────────────────────────────────────────┐   ║
║  │  Filtros:                                                                │   ║
║  │  Estabelecimento: [Matriz ▼]          Departamento: [Financeiro ▼]      │   ║
║  │  Status: [Ativo ▼]                    Buscar: [___________________]     │   ║
║  │                                                                          │   ║
║  │  ☑️ Selecionar Todos (127 colaboradores)                                │   ║
║  │  ────────────────────────────────────────────────────────────────────   │   ║
║  │  ☑️ [Departamento: Financeiro] (45 colaboradores)  [Expandir ▼]        │   ║
║  │  ☑️ [Departamento: RH] (32 colaboradores)           [Expandir ▼]        │   ║
║  │  ☐ [Departamento: TI] (28 colaboradores)            [Expandir ▼]        │   ║
║  │  ☑️ [Departamento: Contabilidade] (22 colaboradores)[Expandir ▼]        │   ║
║  │                                                                          │   ║
║  │  OU                                                                      │   ║
║  │                                                                          │   ║
║  │  Seleção Individual:                                                     │   ║
║  │  ┌──────────────────────────────────────────────────────────────────┐  │   ║
║  │  │ ☑️ João Silva Santos          | Financeiro    | Analista         │  │   ║
║  │  │ ☑️ Maria Oliveira Costa       | Financeiro    | Assistente       │  │   ║
║  │  │ ☐ Pedro Henrique Souza        | TI            | Desenvolvedor    │  │   ║
║  │  │ ☑️ Ana Paula Ferreira         | RH            | Coordenadora     │  │   ║
║  │  │ ☑️ Carlos Eduardo Lima        | Contabilidade | Contador         │  │   ║
║  │  │ ... (mais 122 colaboradores)                                      │  │   ║
║  │  └──────────────────────────────────────────────────────────────────┘  │   ║
║  └────────────────────────────────────────────────────────────────────────┘   ║
║                                                                                 ║
║  📊 Resumo: 99 colaboradores selecionados                                      ║
║                                                                                 ║
║  [Cancelar]                                    [Aplicar Jornada aos 99 colaboradores]║
║                                                                                 ║
╚════════════════════════════════════════════════════════════════════════════════╝
```

---

### 3.4 Tela: Visualização no Perfil do Colaborador

```
╔════════════════════════════════════════════════════════════════╗
║  Colaborador: João Silva Santos                                ║
╠════════════════════════════════════════════════════════════════╣
║                                                                 ║
║  📋 Dados Pessoais                                             ║
║  CPF: 123.456.789-00  |  PIS: 123.45678.90-1                  ║
║  Cargo: Analista Financeiro                                    ║
║  Departamento: Financeiro  |  Estabelecimento: Matriz          ║
║                                                                 ║
║  ⏰ Jornada de Trabalho Atual                                   ║
║  ┌────────────────────────────────────────────────────────┐   ║
║  │  📋 Modelo: Administrativo Padrão                       │   ║
║  │  Tipo: Semanal | 40h/semana                            │   ║
║  │  Vigência: 01/11/2025 até (sem fim)                    │   ║
║  │                                                         │   ║
║  │  Horários:                                              │   ║
║  │  • Segunda a Sexta: 08:00-12:00 | 13:00-17:00          │   ║
║  │  • Sábado e Domingo: Folga                             │   ║
║  │                                                         │   ║
║  │  [Alterar Jornada]  [Ver Histórico]                    │   ║
║  └────────────────────────────────────────────────────────┘   ║
║                                                                 ║
║  📊 Registros de Ponto                                         ║
║  ...                                                           ║
║                                                                 ║
╚════════════════════════════════════════════════════════════════╝
```

---

## 🔧 4. Plano de Implementação

### Fase 1: Preparação do Banco de Dados (1-2 dias)

#### 1.1 Criar Migrations
- `create_work_shift_templates_table.php`
- `create_template_weekly_schedules_table.php`
- `create_template_rotating_rules_table.php`
- `create_employee_work_shift_assignments_table.php`
- `alter_work_schedules_add_source_template.php`

#### 1.2 Executar Migrations
```bash
php artisan migrate
```

---

### Fase 2: Modelos Eloquent (1 dia)

Criar os modelos:
- `WorkShiftTemplate.php`
- `TemplateWeeklySchedule.php`
- `TemplateRotatingRule.php`
- `EmployeeWorkShiftAssignment.php`

Definir relacionamentos:
```php
// WorkShiftTemplate
public function weeklySchedules(): HasMany
public function rotatingRule(): HasOne
public function assignments(): HasMany
public function employees(): BelongsToMany

// Employee (adicionar)
public function currentWorkShiftAssignment(): HasOne
public function workShiftAssignments(): HasMany
```

---

### Fase 3: Seeders com Presets (1 dia)

#### 3.1 Criar Seeder: `WorkShiftPresetsSeeder.php`

Cadastra os presets:
1. Comercial (44h/semana)
2. Administrativo (40h/semana)
3. Escala 12x36
4. Escala 6x1

```bash
php artisan db:seed --class=WorkShiftPresetsSeeder
```

---

### Fase 4: Services (Lógica de Negócio) (2-3 dias)

#### 4.1 `WorkShiftTemplateService.php`
- `createTemplate($data)`: Cria um novo modelo
- `updateTemplate($id, $data)`: Atualiza um modelo
- `deleteTemplate($id)`: Deleta (se permitido)
- `duplicateTemplate($id, $newName)`: Duplica um modelo
- `getTemplatesWithStats()`: Lista com contagem de colaboradores

#### 4.2 `WorkShiftAssignmentService.php`
- `assignToEmployees($templateId, $employeeIds, $dates)`: Atribui em massa
- `unassignFromEmployee($employeeId)`: Remove atribuição
- `getEmployeeScheduleForDate($employeeId, $date)`: Retorna horários do dia
- `calculateRotatingShiftDays($templateId, $cycleStartDate, $dateRange)`: Calcula dias de trabalho

#### 4.3 `RotatingShiftCalculatorService.php`
- `isWorkingDay($date, $cycleStartDate, $workDays, $restDays)`: Verifica se trabalha
- `getWorkingDaysInRange($startDate, $endDate, ...)`: Lista dias de trabalho
- `getNextWorkDay($currentDate, ...)`: Próximo dia de trabalho
- `getNextRestDay($currentDate, ...)`: Próximo dia de folga

---

### Fase 5: Controllers (2 dias)

#### 5.1 `WorkShiftTemplateController.php`
```php
- index()         // Lista modelos
- create()        // Formulário de criação
- store()         // Salva novo modelo
- edit($id)       // Formulário de edição
- update($id)     // Atualiza modelo
- destroy($id)    // Deleta modelo
- show($id)       // Visualiza detalhes
- presets()       // Lista presets disponíveis
```

#### 5.2 `WorkShiftAssignmentController.php`
```php
- index()                    // Tela de atribuição em massa
- assign(Request $request)   // Processa atribuição
- history($employeeId)       // Histórico de jornadas de um colaborador
- bulk_unassign()            // Remove atribuições em massa
```

---

### Fase 6: Rotas e Views (3-4 dias)

#### 6.1 Rotas (`routes/web.php`)
```php
Route::prefix('work-shifts')->name('work-shifts.')->group(function () {
    // Templates
    Route::resource('templates', WorkShiftTemplateController::class);
    Route::get('templates/{id}/duplicate', [WorkShiftTemplateController::class, 'duplicate'])->name('templates.duplicate');
    Route::get('presets', [WorkShiftTemplateController::class, 'presets'])->name('presets');
    
    // Atribuições
    Route::get('assign', [WorkShiftAssignmentController::class, 'index'])->name('assign.index');
    Route::post('assign', [WorkShiftAssignmentController::class, 'assign'])->name('assign.store');
    Route::get('employees/{id}/history', [WorkShiftAssignmentController::class, 'history'])->name('history');
    Route::delete('unassign', [WorkShiftAssignmentController::class, 'bulk_unassign'])->name('unassign');
});
```

#### 6.2 Views (usando Blade)
- `resources/views/work-shifts/templates/index.blade.php`
- `resources/views/work-shifts/templates/create.blade.php`
- `resources/views/work-shifts/templates/edit.blade.php`
- `resources/views/work-shifts/templates/show.blade.php`
- `resources/views/work-shifts/assign/index.blade.php`
- `resources/views/work-shifts/assign/history.blade.php`

---

### Fase 7: Jobs e Comandos Agendados (1-2 dias)

#### 7.1 Job: `GenerateRotatingShiftSchedules.php`
Gera horários futuros para escalas rotativas (executa diariamente).

```php
php artisan schedule:run
```

#### 7.2 Comando: `work-shifts:generate-schedules`
Permite gerar manualmente.

```bash
php artisan work-shifts:generate-schedules --days=90
```

---

### Fase 8: Testes (2-3 dias)

#### 8.1 Testes Unitários
- `RotatingShiftCalculatorServiceTest.php`: Testa algoritmo de cálculo
- `WorkShiftTemplateServiceTest.php`: Testa criação/edição de templates

#### 8.2 Testes de Feature
- `WorkShiftTemplateManagementTest.php`: CRUD de templates
- `BulkAssignmentTest.php`: Atribuição em massa
- `EmployeeScheduleCalculationTest.php`: Cálculo de horários

```bash
php artisan test --filter=WorkShift
```

---

### Fase 9: Documentação e Treinamento (1 dia)

#### 9.1 Documentação Técnica
- API dos Services
- Estrutura do banco
- Exemplos de uso

#### 9.2 Manual do Usuário
- Como criar modelos
- Como atribuir em massa
- Como consultar jornadas

---

## 📊 Resumo de Esforço

| Fase | Descrição | Tempo Estimado |
|------|-----------|----------------|
| 1 | Migrations | 1-2 dias |
| 2 | Modelos Eloquent | 1 dia |
| 3 | Seeders (Presets) | 1 dia |
| 4 | Services | 2-3 dias |
| 5 | Controllers | 2 dias |
| 6 | Rotas e Views | 3-4 dias |
| 7 | Jobs/Comandos | 1-2 dias |
| 8 | Testes | 2-3 dias |
| 9 | Documentação | 1 dia |
| **TOTAL** | | **14-20 dias** |

---

## 🎯 Benefícios da Solução

### Para Gestores:
✅ Configurar jornadas para **centenas de colaboradores em minutos**  
✅ Alterar horários de um departamento inteiro com **1 clique**  
✅ Presets prontos para uso imediato  
✅ Visualização clara de quantos colaboradores usam cada modelo  

### Para o Sistema:
✅ Redução de **99% no tempo de cadastro** de horários  
✅ Eliminação de erros de digitação repetitiva  
✅ Manutenção simplificada (alterar 1 template vs 600 registros)  
✅ Escalabilidade para empresas com milhares de colaboradores  

### Para Colaboradores:
✅ Transparência sobre sua jornada  
✅ Histórico de alterações  
✅ Previsibilidade em escalas rotativas  

---

## 🔐 Considerações de Segurança

1. **Permissões:**
   - Apenas gestores/RH podem criar/editar templates
   - Apenas gestores podem fazer atribuições em massa
   - Logs de auditoria para todas as alterações

2. **Validações:**
   - Não permitir deletar templates em uso
   - Validar que datas de vigência não se sobreponham
   - Validar carga horária semanal vs CLT

3. **Integridade:**
   - Foreign keys com `ON DELETE RESTRICT` para templates
   - Soft deletes para histórico
   - Backup automático antes de atribuições em massa

---

## 🚀 Próximos Passos (Futuro)

- **Integração com eSocial:** Exportar jornadas no formato exigido
- **Notificações:** Avisar colaboradores sobre mudanças de jornada
- **Aprovação em múltiplas etapas:** Gestor solicita → RH aprova
- **Importação de jornadas via CSV:** Para migração de sistemas legados
- **Dashboard analítico:** Visualizar distribuição de jornadas na empresa

---

**Documento criado em:** 30/10/2025  
**Versão:** 1.0  
**Status:** Pronto para implementação
