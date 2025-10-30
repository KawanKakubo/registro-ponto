@extends('layouts.app')
@section('title', 'Colaboradores')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Colaboradores</h1>
    <div class="space-x-2">
        <a href="{{ route('employee-imports.create') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            📊 Importar CSV
        </a>
        <a href="{{ route('employees.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            ➕ Novo Colaborador
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded shadow p-4 mb-6">
    <form method="GET" action="{{ route('employees.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estabelecimento</label>
            <select name="establishment_id" id="establishment_id" class="w-full border rounded px-3 py-2">
                <option value="">Todos</option>
                @foreach($establishments as $establishment)
                    <option value="{{ $establishment->id }}" {{ request('establishment_id') == $establishment->id ? 'selected' : '' }}>
                        {{ $establishment->trade_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
            <select name="department_id" id="department_id" class="w-full border rounded px-3 py-2">
                <option value="">Todos</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status do Cadastro</label>
            <select name="incomplete" class="w-full border rounded px-3 py-2">
                <option value="">Todos</option>
                <option value="1" {{ request('incomplete') == '1' ? 'selected' : '' }}>⚠️ Apenas Incompletos</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar (Nome/CPF/Matrícula)</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Digite nome, CPF ou matrícula..."
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="flex items-end space-x-2">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex-1">
                🔍 Filtrar
            </button>
            <a href="{{ route('employees.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                ✖ Limpar
            </a>
        </div>
    </form>
</div>
<div class="bg-white rounded shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CPF</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departamento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dados</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($employees as $employee)
            @php
                $isIncomplete = empty($employee->department_id) || empty($employee->position);
            @endphp
            <tr class="{{ $isIncomplete ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-gray-50' }}">
                <td class="px-6 py-4">
                    {{ $employee->full_name }}
                    @if($isIncomplete)
                        <span class="ml-2 text-yellow-600 text-xs">⚠️</span>
                    @endif
                </td>
                <td class="px-6 py-4">{{ $employee->cpf }}</td>
                <td class="px-6 py-4">{{ $employee->department->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded @if($employee->status == 'active') bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($employee->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($isIncomplete)
                        <span class="text-xs text-yellow-700 font-medium">Incompleto</span>
                        <div class="text-xs text-gray-500 mt-1">
                            @if(empty($employee->department_id))<span class="mr-1">• Depto.</span>@endif
                            @if(empty($employee->position))<span class="mr-1">• Cargo</span>@endif
                        </div>
                    @else
                        <span class="text-xs text-green-700 font-medium">✓ Completo</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('employees.show', $employee) }}" class="text-blue-600 hover:underline">Ver</a>
                    <a href="{{ route('employees.edit', $employee) }}" class="text-yellow-600 hover:underline">Editar</a>
                    <a href="{{ route('employees.work-schedules.index', $employee) }}" class="text-purple-600 hover:underline">Horários</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum colaborador cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Paginação -->
    @if($employees->hasPages())
    <div class="px-6 py-4 border-t">
        {{ $employees->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Script para Filtros em Cascata -->
<script>
document.getElementById('establishment_id').addEventListener('change', function() {
    const establishmentId = this.value;
    const departmentSelect = document.getElementById('department_id');
    
    // Limpar departamentos
    departmentSelect.innerHTML = '<option value="">Todos</option>';
    
    if (establishmentId) {
        // Buscar departamentos do estabelecimento selecionado
        fetch(`/api/departments?establishment_id=${establishmentId}`)
            .then(response => response.json())
            .then(departments => {
                departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.name;
                    departmentSelect.appendChild(option);
                });
            });
    }
});
</script>
@endsection
