<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Establishment;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    /**
     * Retorna departamentos por estabelecimento
     */
    public function getDepartmentsByEstablishment(Request $request)
    {
        $establishmentId = $request->input('establishment_id');
        
        $departments = Department::where('establishment_id', $establishmentId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }

    /**
     * Busca colaboradores com autocomplete
     */
    public function searchEmployees(Request $request)
    {
        $query = Employee::with(['establishment:id,trade_name', 'department:id,name']);

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

        $employees = $query->select('id', 'full_name', 'cpf', 'establishment_id', 'department_id')
            ->limit(20)
            ->get();

        return response()->json($employees);
    }

    /**
     * Retorna todos os estabelecimentos
     */
    public function getEstablishments()
    {
        $establishments = Establishment::select('id', 'trade_name', 'company_name')
            ->orderBy('trade_name')
            ->get();

        return response()->json($establishments);
    }

    /**
     * Retorna colaboradores por departamento (para multi-select)
     */
    public function getEmployeesByDepartment(Request $request)
    {
        $departmentId = $request->input('department_id');
        
        if (!$departmentId) {
            return response()->json([]);
        }

        $employees = Employee::where('department_id', $departmentId)
            ->where('status', 'active')
            ->select('id', 'full_name', 'cpf', 'matricula')
            ->orderBy('full_name')
            ->get()
            ->map(function ($employee) {
                return [
                    'value' => $employee->id,
                    'text' => $employee->full_name,
                    'cpf' => $employee->cpf_formatted,
                    'matricula' => $employee->matricula ?? '-'
                ];
            });

        return response()->json($employees);
    }
}
