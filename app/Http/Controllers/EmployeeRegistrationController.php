<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\EmployeeRegistration;
use App\Models\Establishment;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeRegistrationController extends Controller
{
    /**
     * Form para criar novo vínculo para uma pessoa
     */
    public function create(Person $person)
    {
        $establishments = Establishment::orderBy('corporate_name')->get();
        $departments = Department::orderBy('name')->get();
        
        return view('employee_registrations.create', compact('person', 'establishments', 'departments'));
    }

    /**
     * Armazenar novo vínculo
     */
    public function store(Request $request, Person $person)
    {
        $validated = $request->validate([
            'matricula' => 'required|string|max:20|unique:employee_registrations,matricula',
            'establishment_id' => 'required|exists:establishments,id',
            'department_id' => 'nullable|exists:departments,id',
            'admission_date' => 'required|date',
            'position' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,on_leave',
        ], [
            'matricula.required' => 'A matrícula é obrigatória.',
            'matricula.unique' => 'Esta matrícula já está em uso.',
            'establishment_id.required' => 'O estabelecimento é obrigatório.',
            'admission_date.required' => 'A data de admissão é obrigatória.',
        ]);

        $registration = EmployeeRegistration::create([
            'person_id' => $person->id,
            'matricula' => $validated['matricula'],
            'establishment_id' => $validated['establishment_id'],
            'department_id' => $validated['department_id'] ?? null,
            'admission_date' => $validated['admission_date'],
            'position' => $validated['position'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('employees.show', $person)
            ->with('success', "Vínculo (Matrícula: {$registration->matricula}) criado com sucesso!");
    }

    /**
     * Form para editar vínculo
     */
    public function edit(EmployeeRegistration $registration)
    {
        $registration->load('person');
        $establishments = Establishment::orderBy('corporate_name')->get();
        $departments = Department::orderBy('name')->get();
        
        return view('employee_registrations.edit', compact('registration', 'establishments', 'departments'));
    }

    /**
     * Atualizar vínculo
     */
    public function update(Request $request, EmployeeRegistration $registration)
    {
        $validated = $request->validate([
            'matricula' => 'required|string|max:20|unique:employee_registrations,matricula,' . $registration->id,
            'establishment_id' => 'required|exists:establishments,id',
            'department_id' => 'nullable|exists:departments,id',
            'admission_date' => 'required|date',
            'position' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        $registration->update($validated);

        return redirect()->route('employees.show', $registration->person)
            ->with('success', 'Vínculo atualizado com sucesso!');
    }

    /**
     * Encerrar vínculo (mudar status para inactive)
     */
    public function terminate(EmployeeRegistration $registration)
    {
        $registration->update(['status' => 'inactive']);

        return redirect()->route('employees.show', $registration->person)
            ->with('success', 'Vínculo encerrado com sucesso!');
    }

    /**
     * Reativar vínculo
     */
    public function reactivate(EmployeeRegistration $registration)
    {
        $registration->update(['status' => 'active']);

        return redirect()->route('employees.show', $registration->person)
            ->with('success', 'Vínculo reativado com sucesso!');
    }

    /**
     * Excluir vínculo permanentemente
     */
    public function destroy(EmployeeRegistration $registration)
    {
        // Verificar se tem registros de ponto
        $hasTimeRecords = DB::table('time_records')
            ->where('employee_registration_id', $registration->id)
            ->exists();

        if ($hasTimeRecords) {
            return back()->with('error', 'Não é possível excluir este vínculo pois possui registros de ponto. Considere encerrá-lo ao invés de excluir.');
        }

        $person = $registration->person;
        $matricula = $registration->matricula;
        
        $registration->delete();

        return redirect()->route('employees.show', $person)
            ->with('success', "Vínculo (Matrícula: {$matricula}) excluído com sucesso!");
    }
}
