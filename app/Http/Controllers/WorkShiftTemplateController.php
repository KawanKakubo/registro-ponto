<?php

namespace App\Http\Controllers;

use App\Models\WorkShiftTemplate;
use App\Models\TemplateWeeklySchedule;
use App\Services\WorkShiftTemplateService;
use Illuminate\Http\Request;

class WorkShiftTemplateController extends Controller
{
    protected $templateService;

    public function __construct(WorkShiftTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    public function index()
    {
        $templates = WorkShiftTemplate::with('weeklySchedules', 'employees')
            ->withCount('employees')
            ->orderBy('is_preset', 'desc')
            ->orderBy('name')
            ->get();
        
        return view('work-shift-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('work-shift-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:work_shift_templates,name',
            'type' => 'required|in:weekly,rotating_shift',
            'weekly_hours' => 'required|numeric|min:0|max:168',
            'schedules' => 'required_if:type,weekly|array',
            'schedules.*.day_of_week' => 'required_if:type,weekly|integer|between:0,6',
            'schedules.*.entry_1' => 'nullable|date_format:H:i',
            'schedules.*.exit_1' => 'nullable|date_format:H:i',
            'schedules.*.entry_2' => 'nullable|date_format:H:i',
            'schedules.*.exit_2' => 'nullable|date_format:H:i',
            'schedules.*.is_work_day' => 'boolean',
        ]);

        try {
            $template = $this->templateService->createTemplate($validated);

            return redirect()->route('work-shift-templates.index')
                ->with('success', "✅ Template '{$template->name}' criado com sucesso!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erro ao criar template: ' . $e->getMessage());
        }
    }

    public function edit(WorkShiftTemplate $template)
    {
        if ($template->is_preset) {
            return redirect()->route('work-shift-templates.index')
                ->with('error', '❌ Templates pré-configurados não podem ser editados. Duplique-o para criar uma versão personalizada.');
        }

        $template->load('weeklySchedules');
        return view('work-shift-templates.edit', compact('template'));
    }

    public function update(Request $request, WorkShiftTemplate $template)
    {
        if ($template->is_preset) {
            return redirect()->route('work-shift-templates.index')
                ->with('error', '❌ Templates pré-configurados não podem ser editados.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:work_shift_templates,name,' . $template->id,
            'weekly_hours' => 'required|numeric|min:0|max:168',
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|integer|between:0,6',
            'schedules.*.entry_1' => 'nullable|date_format:H:i',
            'schedules.*.exit_1' => 'nullable|date_format:H:i',
            'schedules.*.entry_2' => 'nullable|date_format:H:i',
            'schedules.*.exit_2' => 'nullable|date_format:H:i',
            'schedules.*.is_work_day' => 'boolean',
        ]);

        try {
            $template = $this->templateService->updateTemplate($template->id, $validated);

            return redirect()->route('work-shift-templates.index')
                ->with('success', "✅ Template '{$template->name}' atualizado com sucesso!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erro ao atualizar template: ' . $e->getMessage());
        }
    }

    public function destroy(WorkShiftTemplate $template)
    {
        if ($template->is_preset) {
            return redirect()->route('work-shift-templates.index')
                ->with('error', '❌ Templates pré-configurados não podem ser excluídos.');
        }

        if ($template->employees()->count() > 0) {
            return redirect()->route('work-shift-templates.index')
                ->with('error', '❌ Este template não pode ser excluído pois está em uso por ' . $template->employees()->count() . ' colaborador(es).');
        }

        try {
            $template->delete();
            return redirect()->route('work-shift-templates.index')
                ->with('success', '✅ Template excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('work-shift-templates.index')
                ->with('error', 'Erro ao excluir template: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de aplicação em massa
     */
    public function bulkAssignForm()
    {
        $templates = WorkShiftTemplate::orderBy('is_preset', 'desc')->orderBy('name')->get();
        $establishments = \App\Models\Establishment::with('departments')->get();
        
        return view('work-shift-templates.bulk-assign', compact('templates', 'establishments'));
    }

    /**
     * Processa aplicação em massa
     */
    public function bulkAssignStore(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:work_shift_templates,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'effective_from' => 'nullable|date',
        ]);

        try {
            $template = WorkShiftTemplate::with('weeklySchedules')->find($validated['template_id']);
            $employeeIds = $validated['employee_ids'];
            $effectiveFrom = $validated['effective_from'] ?? now()->format('Y-m-d');
            
            $successCount = 0;
            $errors = [];

            foreach ($employeeIds as $employeeId) {
                try {
                    $employee = \App\Models\Employee::find($employeeId);
                    
                    // Remove horários antigos
                    $employee->workSchedules()->delete();
                    
                    // Remove atribuição antiga
                    $employee->workShiftAssignments()->where('effective_until', null)->update([
                        'effective_until' => now()->subDay(),
                    ]);

                    // Cria nova atribuição
                    $employee->workShiftAssignments()->create([
                        'template_id' => $template->id,
                        'effective_from' => $effectiveFrom,
                        'cycle_start_date' => $effectiveFrom,
                        'effective_until' => null,
                        'assigned_by' => 1, // TODO: usar auth()->id()
                        'assigned_at' => now(),
                    ]);

                    // Cria work_schedules
                    if ($template->type === 'weekly' && $template->weeklySchedules->count() > 0) {
                        foreach ($template->weeklySchedules as $schedule) {
                            \App\Models\WorkSchedule::create([
                                'employee_id' => $employee->id,
                                'day_of_week' => $schedule->day_of_week,
                                'entry_1' => $schedule->entry_1,
                                'exit_1' => $schedule->exit_1,
                                'entry_2' => $schedule->entry_2,
                                'exit_2' => $schedule->exit_2,
                                'entry_3' => $schedule->entry_3,
                                'exit_3' => $schedule->exit_3,
                                'source_template_id' => $template->id,
                                'effective_from' => $effectiveFrom,
                            ]);
                        }
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Erro ao aplicar para {$employee->full_name}: " . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                $message = "✅ Jornada '{$template->name}' aplicada com sucesso a {$successCount} colaborador(es)!";
                if (count($errors) > 0) {
                    $message .= " (Com " . count($errors) . " erro(s))";
                }
                return redirect()->route('work-shift-templates.bulk-assign')
                    ->with('success', $message)
                    ->with('errors', $errors);
            } else {
                return redirect()->route('work-shift-templates.bulk-assign')
                    ->with('error', 'Nenhum colaborador foi atualizado.')
                    ->with('errors', $errors);
            }
        } catch (\Exception $e) {
            return redirect()->route('work-shift-templates.bulk-assign')
                ->with('error', 'Erro ao aplicar templates: ' . $e->getMessage());
        }
    }
}
