# 📝 Exemplo de Controller - Módulo de Jornadas

## WorkShiftTemplateController.php

```php
<?php

namespace App\Http\Controllers;

use App\Services\WorkShiftTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkShiftTemplateController extends Controller
{
    protected WorkShiftTemplateService $service;

    public function __construct(WorkShiftTemplateService $service)
    {
        $this->service = $service;
    }

    /**
     * Lista todos os templates
     */
    public function index()
    {
        $templates = $this->service->getTemplatesWithStats();
        
        return view('work-shifts.templates.index', compact('templates'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        $presets = $this->service->getPresets();
        
        return view('work-shifts.templates.create', compact('presets'));
    }

    /**
     * Salva um novo template
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:work_shift_templates,name',
            'description' => 'nullable|string',
            'type' => 'required|in:weekly,rotating_shift',
            'weekly_hours' => 'nullable|numeric|min:0|max:168',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();
            $template = $this->service->createTemplate($data);

            return redirect()
                ->route('work-shifts.templates.show', $template->id)
                ->with('success', 'Template criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao criar template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Exibe detalhes de um template
     */
    public function show($id)
    {
        $template = \App\Models\WorkShiftTemplate::with(['weeklySchedules', 'rotatingRule'])
            ->findOrFail($id);
        
        $employeesCount = $template->getCurrentEmployeesCount();
        
        return view('work-shifts.templates.show', compact('template', 'employeesCount'));
    }

    /**
     * Exibe formulário de edição
     */
    public function edit($id)
    {
        $template = \App\Models\WorkShiftTemplate::with(['weeklySchedules', 'rotatingRule'])
            ->findOrFail($id);
        
        if ($template->is_preset) {
            return redirect()->back()
                ->with('error', 'Templates preset não podem ser editados. Duplique-o para criar uma versão personalizada.');
        }
        
        return view('work-shifts.templates.edit', compact('template'));
    }

    /**
     * Atualiza um template
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:work_shift_templates,name,' . $id,
            'description' => 'nullable|string',
            'weekly_hours' => 'nullable|numeric|min:0|max:168',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $template = $this->service->updateTemplate($id, $request->all());

            return redirect()
                ->route('work-shifts.templates.show', $template->id)
                ->with('success', 'Template atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Deleta um template
     */
    public function destroy($id)
    {
        try {
            $this->service->deleteTemplate($id);

            return redirect()
                ->route('work-shifts.templates.index')
                ->with('success', 'Template deletado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao deletar template: ' . $e->getMessage());
        }
    }

    /**
     * Duplica um template
     */
    public function duplicate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_name' => 'required|string|max:100|unique:work_shift_templates,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $newTemplate = $this->service->duplicateTemplate($id, $request->new_name);

            return redirect()
                ->route('work-shifts.templates.edit', $newTemplate->id)
                ->with('success', 'Template duplicado com sucesso! Você pode editá-lo agora.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao duplicar template: ' . $e->getMessage());
        }
    }

    /**
     * Lista apenas os presets
     */
    public function presets()
    {
        $presets = $this->service->getPresets();
        
        return view('work-shifts.presets', compact('presets'));
    }
}
```

---

## WorkShiftAssignmentController.php

```php
<?php

namespace App\Http\Controllers;

use App\Services\WorkShiftAssignmentService;
use App\Services\WorkShiftTemplateService;
use App\Models\Employee;
use App\Models\Establishment;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkShiftAssignmentController extends Controller
{
    protected WorkShiftAssignmentService $assignmentService;
    protected WorkShiftTemplateService $templateService;

    public function __construct(
        WorkShiftAssignmentService $assignmentService,
        WorkShiftTemplateService $templateService
    ) {
        $this->assignmentService = $assignmentService;
        $this->templateService = $templateService;
    }

    /**
     * Tela de atribuição em massa
     */
    public function index(Request $request)
    {
        $templates = $this->templateService->getTemplatesWithStats();
        $establishments = Establishment::all();
        $departments = Department::all();
        
        // Filtros
        $query = Employee::query()->active();
        
        if ($request->has('establishment_id') && $request->establishment_id) {
            $query->where('establishment_id', $request->establishment_id);
        }
        
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->has('search') && $request->search) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }
        
        $employees = $query->with(['establishment', 'department', 'currentWorkShiftAssignment.template'])
            ->orderBy('full_name')
            ->paginate(50);
        
        return view('work-shifts.assign.index', compact(
            'templates',
            'establishments',
            'departments',
            'employees'
        ));
    }

    /**
     * Processa a atribuição em massa
     */
    public function assign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:work_shift_templates,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'cycle_start_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $dates = [
                'effective_from' => $request->effective_from,
                'effective_until' => $request->effective_until,
                'cycle_start_date' => $request->cycle_start_date,
                'generate_schedules' => $request->has('generate_schedules'),
            ];

            $result = $this->assignmentService->assignToEmployees(
                $request->template_id,
                $request->employee_ids,
                $dates
            );

            $message = "Jornada atribuída a {$result['assigned_count']} colaborador(es)";
            
            if (count($result['errors']) > 0) {
                $message .= ". {count($result['errors'])} erro(s) ocorreram.";
            }

            return redirect()
                ->route('work-shifts.assign.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atribuir jornadas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Histórico de jornadas de um colaborador
     */
    public function history($employeeId)
    {
        $employee = Employee::with(['establishment', 'department'])->findOrFail($employeeId);
        $history = $this->assignmentService->getEmployeeHistory($employeeId);
        
        return view('work-shifts.assign.history', compact('employee', 'history'));
    }

    /**
     * Remove atribuições em massa
     */
    public function bulk_unassign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $count = 0;
            foreach ($request->employee_ids as $employeeId) {
                if ($this->assignmentService->unassignFromEmployee($employeeId)) {
                    $count++;
                }
            }

            return redirect()
                ->route('work-shifts.assign.index')
                ->with('success', "Jornada removida de {$count} colaborador(es)");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao remover jornadas: ' . $e->getMessage());
        }
    }
}
```

---

## Exemplo de uso em uma API (opcional)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WorkShiftAssignmentService;
use Illuminate\Http\Request;

class WorkShiftApiController extends Controller
{
    protected WorkShiftAssignmentService $service;

    public function __construct(WorkShiftAssignmentService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/employees/{id}/schedule/{date}
     * Retorna o horário de um colaborador em uma data específica
     */
    public function getSchedule($employeeId, $date)
    {
        try {
            $schedule = $this->service->getEmployeeScheduleForDate($employeeId, $date);

            if (!$schedule) {
                return response()->json([
                    'message' => 'Colaborador não tem jornada atribuída ou está de folga nesta data',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'message' => 'Horário encontrado',
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar horário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/employees/{id}/schedule/month/{year}/{month}
     * Retorna todos os dias de trabalho de um colaborador em um mês
     */
    public function getMonthSchedule($employeeId, $year, $month)
    {
        try {
            $startDate = "{$year}-{$month}-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $days = [];
            $currentDate = $startDate;
            
            while ($currentDate <= $endDate) {
                $schedule = $this->service->getEmployeeScheduleForDate($employeeId, $currentDate);
                
                $days[] = [
                    'date' => $currentDate,
                    'is_work_day' => $schedule !== null,
                    'schedule' => $schedule
                ];
                
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }

            return response()->json([
                'message' => 'Horários do mês',
                'data' => [
                    'employee_id' => $employeeId,
                    'year' => $year,
                    'month' => $month,
                    'days' => $days
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar horários',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

**Nota:** Estes são exemplos de implementação. Ajuste conforme sua arquitetura e necessidades específicas.
