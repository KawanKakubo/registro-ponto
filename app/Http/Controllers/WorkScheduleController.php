<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use App\Models\Employee;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    public function index(Employee $employee)
    {
        $schedules = $employee->workSchedules()->orderBy('day_of_week')->get();
        return view('work-schedules.index', compact('employee', 'schedules'));
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
}
