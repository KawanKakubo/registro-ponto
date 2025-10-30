<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Establishment;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('establishment')->orderBy('name')->get();
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        $establishments = Establishment::orderBy('corporate_name')->get();
        return view('departments.create', compact('establishments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'establishment_id' => 'required|exists:establishments,id',
            'name' => 'required|string|max:100',
            'responsible' => 'nullable|string|max:100',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Departamento criado com sucesso!');
    }

    public function show(Department $department)
    {
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $establishments = Establishment::orderBy('corporate_name')->get();
        return view('departments.edit', compact('department', 'establishments'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'establishment_id' => 'required|exists:establishments,id',
            'name' => 'required|string|max:100',
            'responsible' => 'nullable|string|max:100',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Departamento atualizado com sucesso!');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Departamento excluído com sucesso!');
    }
}
