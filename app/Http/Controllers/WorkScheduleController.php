<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use App\Models\Employee;
use App\Models\WorkShiftTemplate;
use App\Services\WorkShiftAssignmentService;
use Illuminate\Http\Request;

/**
 * CONTROLLER DEPRECATED - USE WORKSHIFTTEMPLATECONTROLLER
 * 
 * @deprecated Este controller está obsoleto e mantido apenas para compatibilidade com código legado.
 * 
 * NOVA ABORDAGEM:
 * - Use WorkShiftTemplateController::bulkAssignForm() para atribuir jornadas
 * - Use WorkShiftTemplateController::bulkAssignStore() para processar atribuições
 * - A nova abordagem trabalha com vínculos (EmployeeRegistration) ao invés de Employee
 * 
 * DIFERENÇAS:
 * - Antes: WorkSchedule direto por colaborador (Employee)
 * - Agora: WorkShiftTemplate atribuído a vínculos (EmployeeRegistration) via EmployeeWorkShiftAssignment
 * 
 * BENEFÍCIOS:
 * - Suporte a múltiplos vínculos por pessoa
 * - Histórico de atribuições preservado
 * - Templates reutilizáveis
 * - Suporte a jornadas semanais, escalas rotativas e carga horária flexível
 * 
 * REMOÇÃO PLANEJADA: Versão 2.0
 */
class WorkScheduleController extends Controller
{
    protected $assignmentService;

    public function __construct(WorkShiftAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Employee $employee)
    {
        $schedules = $employee->workSchedules()->orderBy('day_of_week')->get();
        $templates = WorkShiftTemplate::all();
        $currentAssignment = $employee->workShiftAssignments()->active()->first();
        
        return view('work-schedules.index', compact('employee', 'schedules', 'templates', 'currentAssignment'));
    }

    public function create(Employee $employee)
    {
        return view('work-schedules.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'entry_1' => 'nullable|date_format:H:i',
            'exit_1' => 'nullable|date_format:H:i',
            'entry_2' => 'nullable|date_format:H:i',
            'exit_2' => 'nullable|date_format:H:i',
            'entry_3' => 'nullable|date_format:H:i',
            'exit_3' => 'nullable|date_format:H:i',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date',
        ]);

        $validated['employee_id'] = $employee->id;

        WorkSchedule::create($validated);

        return redirect()->route('employees.work-schedules.index', $employee)
            ->with('success', 'Horário de trabalho criado com sucesso!');
    }

    public function edit(Employee $employee, WorkSchedule $workSchedule)
    {
        return view('work-schedules.edit', compact('employee', 'workSchedule'));
    }

    public function update(Request $request, Employee $employee, WorkSchedule $workSchedule)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'entry_1' => 'nullable|date_format:H:i',
            'exit_1' => 'nullable|date_format:H:i',
            'entry_2' => 'nullable|date_format:H:i',
            'exit_2' => 'nullable|date_format:H:i',
            'entry_3' => 'nullable|date_format:H:i',
            'exit_3' => 'nullable|date_format:H:i',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date',
        ]);

        $workSchedule->update($validated);

        return redirect()->route('employees.work-schedules.index', $employee)
            ->with('success', 'Horário de trabalho atualizado com sucesso!');
    }

    public function destroy(Employee $employee, WorkSchedule $workSchedule)
    {
        $workSchedule->delete();

        return redirect()->route('employees.work-schedules.index', $employee)
            ->with('success', 'Horário de trabalho excluído com sucesso!');
    }

    /**
     * Aplica um template de jornada ao colaborador
     */
    public function applyTemplate(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:work_shift_templates,id',
            'effective_from' => 'nullable|date',
            'cycle_start_date' => 'nullable|date',
        ]);

        try {
            // Remove horários antigos
            $employee->workSchedules()->delete();
            
            // Remove atribuição antiga
            $employee->workShiftAssignments()->where('effective_until', null)->update([
                'effective_until' => now()->subDay(),
            ]);

            // Aplica o template
            $result = $this->assignmentService->assignToEmployees(
                $validated['template_id'],
                [$employee->id],
                [
                    'effective_from' => $validated['effective_from'] ?? now()->format('Y-m-d'),
                    'cycle_start_date' => $validated['cycle_start_date'] ?? now()->format('Y-m-d'),
                    'effective_until' => null,
                ]
            );

            // Cria os work_schedules baseado no template
            $template = WorkShiftTemplate::with('weeklySchedules')->find($validated['template_id']);
            
            if ($template->type === 'weekly' && $template->weeklySchedules->count() > 0) {
                foreach ($template->weeklySchedules as $schedule) {
                    WorkSchedule::create([
                        'employee_id' => $employee->id,
                        'day_of_week' => $schedule->day_of_week,
                        'entry_1' => $schedule->entry_1,
                        'exit_1' => $schedule->exit_1,
                        'entry_2' => $schedule->entry_2,
                        'exit_2' => $schedule->exit_2,
                        'entry_3' => $schedule->entry_3,
                        'exit_3' => $schedule->exit_3,
                        'source_template_id' => $template->id,
                        'effective_from' => $validated['effective_from'] ?? now(),
                    ]);
                }
            }

            return redirect()->route('employees.work-schedules.index', $employee)
                ->with('success', "✅ Jornada '{$template->name}' aplicada com sucesso!");
        } catch (\Exception $e) {
            return redirect()->route('employees.work-schedules.index', $employee)
                ->with('error', 'Erro ao aplicar template: ' . $e->getMessage());
        }
    }
}
