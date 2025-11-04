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
        $templates = WorkShiftTemplate::with(['weeklySchedules', 'rotatingRule', 'flexibleHours', 'employeeRegistrations'])
            ->withCount('employeeRegistrations')
            ->orderBy('is_preset', 'desc')
            ->orderBy('name')
            ->get();
        
        return view('work-shift-templates.index', compact('templates'));
    }

    public function create()
    {
        // Redireciona para a tela de seleção de tipo
        return redirect()->route('work-shift-templates.select-type');
    }

    /**
     * Tela de seleção do tipo de jornada
     */
    public function selectType()
    {
        return view('work-shift-templates.select-type');
    }

    /**
     * Formulário para jornada semanal fixa
     */
    public function createWeekly()
    {
        return view('work-shift-templates.create-weekly');
    }

    /**
     * Formulário para escala rotativa
     */
    public function createRotating()
    {
        return view('work-shift-templates.create-rotating');
    }

    /**
     * Formulário para carga horária flexível
     */
    public function createFlexible()
    {
        return view('work-shift-templates.create-flexible');
    }

    public function store(Request $request)
    {
        // Validação base
        $baseRules = [
            'name' => 'required|string|max:255|unique:work_shift_templates,name',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:weekly,rotating_shift,weekly_hours',
        ];

        // Adicionar regras específicas por tipo
        if ($request->type === 'weekly') {
            $baseRules['weekly_hours'] = 'required|numeric|min:0|max:168';
            $baseRules['schedules'] = 'required|array';
            $baseRules['schedules.*.day_of_week'] = 'required|integer|between:0,6';
            $baseRules['schedules.*.entry_1'] = 'nullable|date_format:H:i';
            $baseRules['schedules.*.exit_1'] = 'nullable|date_format:H:i';
            $baseRules['schedules.*.entry_2'] = 'nullable|date_format:H:i';
            $baseRules['schedules.*.exit_2'] = 'nullable|date_format:H:i';
            $baseRules['schedules.*.is_work_day'] = 'boolean';
        } elseif ($request->type === 'rotating_shift') {
            $baseRules['work_days'] = 'required|integer|min:1|max:30';
            $baseRules['rest_days'] = 'required|integer|min:1|max:30';
            $baseRules['shift_start_time'] = 'required|date_format:H:i';
            $baseRules['shift_end_time'] = 'required|date_format:H:i';
            $baseRules['validate_exact_hours'] = 'boolean';
            $baseRules['tolerance_minutes'] = 'nullable|integer|min:0|max:120';
        } elseif ($request->type === 'weekly_hours') {
            $baseRules['weekly_hours_required'] = 'required|numeric|min:1|max:168';
            $baseRules['period_type'] = 'required|in:weekly,biweekly,monthly';
            $baseRules['grace_minutes'] = 'nullable|integer|min:0|max:120';
            $baseRules['requires_minimum_daily_hours'] = 'boolean';
            $baseRules['minimum_daily_hours'] = 'nullable|numeric|min:0.5|max:24';
        }

        $validated = $request->validate($baseRules);

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

        // Validação básica comum a todos os tipos
        $rules = [
            'name' => 'required|string|max:255|unique:work_shift_templates,name,' . $template->id,
            'description' => 'nullable|string',
        ];

        // Adicionar validações específicas por tipo
        if ($template->type === 'weekly') {
            $rules['schedules'] = 'required|array';
            $rules['schedules.*.day_of_week'] = 'required|integer|between:0,6';
            $rules['schedules.*.entry_1'] = 'nullable|date_format:H:i';
            $rules['schedules.*.exit_1'] = 'nullable|date_format:H:i';
            $rules['schedules.*.entry_2'] = 'nullable|date_format:H:i';
            $rules['schedules.*.exit_2'] = 'nullable|date_format:H:i';
            $rules['schedules.*.is_work_day'] = 'boolean';
        } elseif ($template->type === 'rotating_shift') {
            $rules['work_days'] = 'required|integer|min:1|max:30';
            $rules['rest_days'] = 'required|integer|min:1|max:30';
            $rules['shift_start_time'] = 'nullable|date_format:H:i';
            $rules['shift_end_time'] = 'nullable|date_format:H:i';
            $rules['shift_duration_hours'] = 'nullable|numeric|min:1|max:24';
        } elseif ($template->type === 'weekly_hours') {
            $rules['weekly_hours_required'] = 'required|numeric|min:1|max:60';
            $rules['period_type'] = 'required|in:weekly,biweekly,monthly';
            $rules['grace_minutes'] = 'nullable|integer|min:0|max:60';
            $rules['requires_minimum_daily_hours'] = 'boolean';
        }

        $validated = $request->validate($rules);

        try {
            // Atualizar dados básicos do template
            $template->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            // Atualizar dados específicos por tipo
            if ($template->type === 'weekly') {
                // Deletar horários antigos
                $template->weeklySchedules()->delete();
                
                // Criar novos horários
                foreach ($validated['schedules'] as $scheduleData) {
                    if (isset($scheduleData['is_work_day']) && $scheduleData['is_work_day']) {
                        $template->weeklySchedules()->create([
                            'day_of_week' => $scheduleData['day_of_week'],
                            'entry_1' => $scheduleData['entry_1'] ?? null,
                            'exit_1' => $scheduleData['exit_1'] ?? null,
                            'entry_2' => $scheduleData['entry_2'] ?? null,
                            'exit_2' => $scheduleData['exit_2'] ?? null,
                            'is_work_day' => true,
                        ]);
                    }
                }
            } elseif ($template->type === 'rotating_shift') {
                $template->rotatingRule()->update([
                    'work_days' => $validated['work_days'],
                    'rest_days' => $validated['rest_days'],
                    'shift_start_time' => $validated['shift_start_time'] ?? null,
                    'shift_end_time' => $validated['shift_end_time'] ?? null,
                    'shift_duration_hours' => $validated['shift_duration_hours'] ?? null,
                ]);
            } elseif ($template->type === 'weekly_hours') {
                $template->flexibleHours()->update([
                    'weekly_hours_required' => $validated['weekly_hours_required'],
                    'period_type' => $validated['period_type'],
                    'grace_minutes' => $validated['grace_minutes'] ?? 0,
                    'requires_minimum_daily_hours' => $validated['requires_minimum_daily_hours'] ?? false,
                ]);
            }

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

        // Verifica se está em uso por vínculos
        $registrationsCount = $template->employeeRegistrations()->count();
        if ($registrationsCount > 0) {
            return redirect()->route('work-shift-templates.index')
                ->with('error', "❌ Este template não pode ser excluído pois está em uso por {$registrationsCount} vínculo(s).");
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
     * Atualizado para trabalhar com vínculos (EmployeeRegistration)
     */
    public function bulkAssignForm()
    {
        $templates = WorkShiftTemplate::orderBy('is_preset', 'desc')->orderBy('name')->get();
        $establishments = \App\Models\Establishment::with('departments')->get();
        
        // Buscar todos os vínculos ativos com eager loading
        $registrations = \App\Models\EmployeeRegistration::with(['person', 'establishment', 'department', 'currentWorkShiftAssignment.template'])
            ->where('status', 'active')
            ->orderBy('matricula')
            ->get();
        
        return view('work-shift-templates.bulk-assign', compact('templates', 'establishments', 'registrations'));
    }

    /**
     * Processa aplicação em massa
     * Atualizado para trabalhar com vínculos (EmployeeRegistration)
     */
    public function bulkAssignStore(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:work_shift_templates,id',
            'registration_ids' => 'required|array|min:1',
            'registration_ids.*' => 'exists:employee_registrations,id',
            'effective_from' => 'nullable|date',
        ]);

        try {
            $template = WorkShiftTemplate::with('weeklySchedules')->find($validated['template_id']);
            $registrationIds = $validated['registration_ids'];
            $effectiveFrom = $validated['effective_from'] ?? now()->format('Y-m-d');
            
            $successCount = 0;
            $errors = [];

            foreach ($registrationIds as $registrationId) {
                try {
                    $registration = \App\Models\EmployeeRegistration::with('person')->find($registrationId);
                    
                    if (!$registration) {
                        $errors[] = "Vínculo #{$registrationId} não encontrado.";
                        continue;
                    }
                    
                    // Remove horários antigos (se houver - DEPRECATED)
                    \App\Models\WorkSchedule::where('employee_id', $registration->person_id)->delete();
                    
                    // Encerra atribuição antiga (se houver)
                    $registration->workShiftAssignments()
                        ->whereNull('effective_until')
                        ->update([
                            'effective_until' => now()->subDay(),
                        ]);

                    // Cria nova atribuição para o vínculo
                    $registration->workShiftAssignments()->create([
                        'template_id' => $template->id,
                        'effective_from' => $effectiveFrom,
                        'cycle_start_date' => $effectiveFrom,
                        'effective_until' => null,
                        'assigned_by' => auth()->id() ?? 1,
                        'assigned_at' => now(),
                    ]);

                    // Cria work_schedules baseado no tipo de jornada (DEPRECATED - mantido por compatibilidade)
                    if ($template->type === 'weekly' && $template->weeklySchedules->count() > 0) {
                        // Jornadas semanais fixas - cria um schedule por dia da semana
                        foreach ($template->weeklySchedules as $schedule) {
                            \App\Models\WorkSchedule::create([
                                'employee_id' => $registration->person_id, // DEPRECATED field
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
                    } elseif ($template->type === 'rotating_shift' || $template->type === 'weekly_hours') {
                        // Para escalas rotativas e carga horária flexível, o WorkShiftAssignmentService 
                        // calculará dinamicamente os horários usando getEmployeeScheduleForDate()
                        // Não precisa criar WorkSchedule records antecipadamente
                        // O cálculo é feito on-the-fly pelo TimesheetGeneratorService
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $personName = $registration->person->full_name ?? 'Desconhecido';
                    $errors[] = "Erro ao aplicar para {$personName} (Matrícula: {$registration->matricula}): " . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                $message = "✅ Jornada '{$template->name}' aplicada com sucesso a {$successCount} vínculo(s)!";
                if (count($errors) > 0) {
                    $message .= " (Com " . count($errors) . " erro(s))";
                }
                return redirect()->route('work-shift-templates.bulk-assign')
                    ->with('success', $message)
                    ->with('errors', $errors);
            } else {
                return redirect()->route('work-shift-templates.bulk-assign')
                    ->with('error', 'Nenhum vínculo foi atualizado.')
                    ->with('errors', $errors);
            }
        } catch (\Exception $e) {
            return redirect()->route('work-shift-templates.bulk-assign')
                ->with('error', 'Erro ao aplicar templates: ' . $e->getMessage());
        }
    }
}
