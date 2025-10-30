<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Establishment;
use App\Models\Department;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['establishment', 'department']);

        // Filtro por estabelecimento
        if ($request->filled('establishment_id')) {
            $query->where('establishment_id', $request->establishment_id);
        }

        // Filtro por departamento
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Busca por nome ou CPF
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'ILIKE', "%{$search}%")
                  ->orWhere('cpf', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por registros incompletos
        if ($request->filled('incomplete') && $request->incomplete == '1') {
            $query->where(function($q) {
                $q->whereNull('department_id')
                  ->orWhereNull('position');
            });
        }

        $employees = $query->orderBy('full_name')->paginate(50);
        $establishments = Establishment::orderBy('trade_name')->get();
        $departments = Department::orderBy('name')->get();

        return view('employees.index', compact('employees', 'establishments', 'departments'));
    }

    public function create()
    {
        $establishments = Establishment::orderBy('corporate_name')->get();
        $departments = Department::orderBy('name')->get();
        return view('employees.create', compact('establishments', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'establishment_id' => 'required|exists:establishments,id',
            'department_id' => 'nullable|exists:departments,id',
            'full_name' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:employees',
            'pis_pasep' => 'nullable|string|max:15|unique:employees',
            'ctps' => 'nullable|string|max:20',
            'admission_date' => 'required|date',
            'position' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Colaborador criado com sucesso!');
    }

    public function show(Employee $employee)
    {
        $employee->load(['establishment', 'department', 'workSchedules', 'timeRecords' => function($query) {
            $query->orderBy('recorded_at', 'desc')->limit(10);
        }]);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $establishments = Establishment::orderBy('corporate_name')->get();
        $departments = Department::orderBy('name')->get();
        return view('employees.edit', compact('employee', 'establishments', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'establishment_id' => 'required|exists:establishments,id',
            'department_id' => 'nullable|exists:departments,id',
            'full_name' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:employees,cpf,' . $employee->id,
            'pis_pasep' => 'nullable|string|max:15|unique:employees,pis_pasep,' . $employee->id,
            'ctps' => 'nullable|string|max:20',
            'admission_date' => 'required|date',
            'position' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Colaborador atualizado com sucesso!');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Colaborador excluído com sucesso!');
    }
}
