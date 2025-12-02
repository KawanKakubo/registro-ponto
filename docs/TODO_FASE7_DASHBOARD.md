# 📊 TODO - FASE 7: DASHBOARD E RELATÓRIOS

**Data de Início**: 03/11/2025  
**Prioridade**: 🔴 Alta  
**Estimativa**: 2 semanas  
**Status**: 🚀 Em Progresso (70% completo)

---

## 🎯 Objetivos da Fase 7

Criar um dashboard moderno e informativo com estatísticas consolidadas, gráficos interativos e relatórios baseados na arquitetura Person + EmployeeRegistrations.

---

## 📋 Checklist de Tarefas

### 1. DashboardController ✅
- [x] Criar `app/Http/Controllers/DashboardController.php`
- [x] Método `index()`: Dashboard principal
  - [x] Estatísticas consolidadas
  - [x] Dados para gráficos
  - [x] Alertas e notificações
- [ ] Método `stats()`: API endpoint para estatísticas (opcional)
- [x] Testes automatizados (6/6 passando)

### 2. Estatísticas Consolidadas ✅
- [x] Total de pessoas cadastradas
- [x] Total de vínculos (ativos, inativos, em afastamento)
- [x] Pessoas sem vínculos ativos (alerta)
- [x] Vínculos sem jornada atribuída (alerta)
- [x] Estabelecimentos com mais vínculos
- [x] Distribuição de vínculos por status
- [x] Registros de ponto do mês atual
- [x] Importações AFD recentes

### 3. Gráficos e Visualizações ✅
- [x] **Gráfico 1**: Vínculos por Estabelecimento (bar chart)
- [x] **Gráfico 2**: Distribuição de Jornadas (pie chart)
- [x] **Gráfico 3**: Timeline de Importações AFD (line chart - 30 dias)
- [x] **Gráfico 4**: Vínculos por Status (donut chart)
- [x] Implementar com Chart.js 4.4.0

### 4. Widgets e Cards ✅
- [x] Card: Pessoas Cadastradas
- [x] Card: Vínculos Ativos
- [x] Card: Estabelecimentos
- [x] Card: Marcações Hoje
- [x] Widget: Alertas (pessoas sem vínculo, vínculos sem jornada)
- [x] Widget: Últimas Importações (atividade recente)
- [ ] Widget: Ações Rápidas

### 5. ReportController (Opcional) ⏳
- [ ] Criar `app/Http/Controllers/ReportController.php`
- [ ] Método `peopleWithoutRegistrations()`: Pessoas sem vínculos ativos
- [ ] Método `registrationsWithoutWorkshift()`: Vínculos sem jornada
- [ ] Método `timeRecordsWithoutRegistration()`: Registros órfãos
- [ ] Método `establishmentReport()`: Relatório por estabelecimento
- [ ] Exportação em Excel/CSV (Laravel Excel)
- [ ] Exportação em PDF (DomPDF)

### 6. Views do Dashboard ⏳
- [ ] Atualizar `resources/views/dashboard.blade.php`
- [ ] Seção de estatísticas consolidadas
- [ ] Seção de gráficos (3-4 gráficos)
- [ ] Seção de alertas e notificações
- [ ] Seção de atividades recentes
- [ ] Seção de ações rápidas
- [ ] Responsividade (mobile-friendly)

### 7. Views de Relatórios (Se implementar ReportController) ⏳
- [ ] `resources/views/reports/index.blade.php`: Lista de relatórios
- [ ] `resources/views/reports/people-without-registrations.blade.php`
- [ ] `resources/views/reports/registrations-without-workshift.blade.php`
- [ ] `resources/views/reports/establishment-report.blade.php`
- [ ] Botões de exportação (Excel, PDF)

### 8. Rotas ⏳
- [ ] Atualizar `routes/web.php`
- [ ] `GET /dashboard` → `DashboardController@index`
- [ ] `GET /reports` → `ReportController@index` (se implementar)
- [ ] `GET /reports/people-without-registrations` → ReportController
- [ ] `GET /reports/registrations-without-workshift` → ReportController
- [ ] `GET /reports/export/excel/{type}` → Exportação Excel
- [ ] `GET /reports/export/pdf/{type}` → Exportação PDF

### 9. Bibliotecas Frontend ⏳
- [ ] Instalar Chart.js via NPM ou CDN
- [ ] Configurar scripts para gráficos
- [ ] Adicionar animações e transições
- [ ] Testar responsividade

### 10. Testes ⏳
- [ ] `tests/Feature/DashboardControllerTest.php`
- [ ] `tests/Feature/ReportControllerTest.php` (se implementar)
- [ ] Testar carregamento de estatísticas
- [ ] Testar geração de relatórios
- [ ] Testar exportações
- [ ] Validar queries de performance

---

## 🎨 Design do Dashboard

### Layout Proposto:

```
┌─────────────────────────────────────────────────────────────┐
│  🎯 Dashboard - Sistema de Registro de Ponto                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐      │
│  │ 👥 1,234│  │ 🔗 2,456│  │ 🏢   12 │  │ 🕐  345 │      │
│  │ Pessoas │  │ Vínculos│  │ Estabel.│  │ Marcações│      │
│  └─────────┘  └─────────┘  └─────────┘  └─────────┘      │
│                                                             │
│  ┌─────────────────────┐  ┌─────────────────────┐         │
│  │ 📊 Vínculos por     │  │ 📈 Distribuição de  │         │
│  │    Estabelecimento  │  │    Jornadas         │         │
│  │                     │  │                     │         │
│  │   [Bar Chart]       │  │   [Pie Chart]       │         │
│  └─────────────────────┘  └─────────────────────┘         │
│                                                             │
│  ┌─────────────────────────────────────────────┐          │
│  │ ⚠️  ALERTAS                                  │          │
│  │  • 15 pessoas sem vínculos ativos            │          │
│  │  • 23 vínculos sem jornada atribuída        │          │
│  └─────────────────────────────────────────────┘          │
│                                                             │
│  ┌─────────────────────────────────────────────┐          │
│  │ 🕐 ATIVIDADES RECENTES                       │          │
│  │  • Importação AFD concluída - 234 registros  │          │
│  │  • Colaborador João Silva cadastrado         │          │
│  └─────────────────────────────────────────────┘          │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Métricas e KPIs

### Estatísticas Principais:
- **Pessoas Cadastradas**: `Person::count()`
- **Vínculos Ativos**: `EmployeeRegistration::where('status', 'active')->count()`
- **Vínculos Inativos**: `EmployeeRegistration::where('status', 'inactive')->count()`
- **Estabelecimentos**: `Establishment::count()`
- **Marcações Hoje**: `TimeRecord::whereDate('recorded_at', today())->count()`

### Alertas:
- **Pessoas sem vínculos**: `Person::doesntHave('activeRegistrations')->count()`
- **Vínculos sem jornada**: `EmployeeRegistration::doesntHave('currentWorkShiftAssignment')->where('status', 'active')->count()`

### Gráficos:
1. **Vínculos por Estabelecimento**:
   ```php
   EmployeeRegistration::selectRaw('establishment_id, count(*) as total')
       ->where('status', 'active')
       ->groupBy('establishment_id')
       ->with('establishment')
       ->get()
   ```

2. **Distribuição de Jornadas**:
   ```php
   WorkShiftTemplate::withCount('employeeRegistrations')
       ->having('employee_registrations_count', '>', 0)
       ->get()
   ```

---

## 🔧 Implementação Técnica

### DashboardController - Estrutura:

```php
class DashboardController extends Controller
{
    public function index()
    {
        // Estatísticas consolidadas
        $stats = [
            'total_people' => Person::count(),
            'active_registrations' => EmployeeRegistration::where('status', 'active')->count(),
            'inactive_registrations' => EmployeeRegistration::where('status', 'inactive')->count(),
            'establishments' => Establishment::count(),
            'today_records' => TimeRecord::whereDate('recorded_at', today())->count(),
        ];
        
        // Alertas
        $alerts = [
            'people_without_registrations' => Person::doesntHave('activeRegistrations')->count(),
            'registrations_without_workshift' => EmployeeRegistration::doesntHave('currentWorkShiftAssignment')
                ->where('status', 'active')
                ->count(),
        ];
        
        // Dados para gráficos
        $charts = [
            'registrations_by_establishment' => $this->getRegistrationsByEstablishment(),
            'workshift_distribution' => $this->getWorkshiftDistribution(),
            'recent_imports' => $this->getRecentImports(),
        ];
        
        return view('dashboard', compact('stats', 'alerts', 'charts'));
    }
    
    private function getRegistrationsByEstablishment()
    {
        return EmployeeRegistration::selectRaw('establishment_id, count(*) as total')
            ->where('status', 'active')
            ->groupBy('establishment_id')
            ->with('establishment')
            ->get()
            ->map(function($item) {
                return [
                    'label' => $item->establishment->corporate_name ?? 'N/A',
                    'value' => $item->total,
                ];
            });
    }
    
    private function getWorkshiftDistribution()
    {
        return WorkShiftTemplate::withCount('employeeRegistrations')
            ->having('employee_registrations_count', '>', 0)
            ->get()
            ->map(function($template) {
                return [
                    'label' => $template->name,
                    'value' => $template->employee_registrations_count,
                ];
            });
    }
    
    private function getRecentImports()
    {
        return AfdImport::orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->groupBy(function($import) {
                return $import->created_at->format('Y-m-d');
            })
            ->map(function($imports) {
                return $imports->count();
            });
    }
}
```

---

## 🧪 Testes

### DashboardControllerTest:

```php
public function test_dashboard_loads_successfully()
{
    $this->actingAs(User::first());
    
    $response = $this->get(route('dashboard'));
    
    $response->assertStatus(200);
    $response->assertViewIs('dashboard');
    $response->assertViewHas(['stats', 'alerts', 'charts']);
}

public function test_dashboard_shows_correct_statistics()
{
    $this->actingAs(User::first());
    
    $peopleCount = Person::count();
    $activeRegistrations = EmployeeRegistration::where('status', 'active')->count();
    
    $response = $this->get(route('dashboard'));
    
    $response->assertSee($peopleCount);
    $response->assertSee($activeRegistrations);
}
```

---

## 📅 Timeline

### Semana 1:
- [ ] Dias 1-2: Criar DashboardController e estatísticas
- [ ] Dias 3-4: Implementar gráficos com Chart.js
- [ ] Dia 5: Atualizar view do dashboard

### Semana 2:
- [ ] Dias 1-2: Criar ReportController (se implementar)
- [ ] Dias 3-4: Implementar exportações (Excel/PDF)
- [ ] Dia 5: Testes e validação final

---

## 🎯 Critérios de Conclusão

- [ ] DashboardController criado e funcional
- [ ] Estatísticas consolidadas implementadas
- [ ] Pelo menos 3 gráficos funcionando
- [ ] Alertas visíveis no dashboard
- [ ] ReportController implementado (opcional)
- [ ] Exportações funcionando (Excel/PDF - opcional)
- [ ] Testes passando (>90%)
- [ ] Documentação atualizada

---

## 📚 Referências

- **Chart.js**: https://www.chartjs.org/
- **Laravel Excel**: https://laravel-excel.com/
- **DomPDF**: https://github.com/barryvdh/laravel-dompdf

---

**Status**: 🚀 Pronto para iniciar!  
**Próximo Passo**: Criar DashboardController
