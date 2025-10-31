@extends('layouts.main')

@section('title', 'Colaboradores')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Gerenciar Colaboradores</h1>
            <p class="text-gray-600 mt-1">Visualize, busque e gerencie todos os colaboradores do sistema</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('employee-imports.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="fas fa-file-import mr-2"></i>Importar CSV
            </a>
            <a href="{{ route('employees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i>Adicionar Colaborador
            </a>
        </div>
    </div>

    <!-- Filtros Avançados -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-filter mr-2 text-blue-600"></i>Filtros Avançados
            </h2>
        </div>

        <form method="GET" action="{{ route('employees.index') }}" x-data="{ establishmentId: '{{ request('establishment_id') }}' }">
            <!-- Busca Grande -->
            <div class="mb-4">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Pesquisar por Nome, CPF ou Matrícula..."
                    class="w-full px-6 py-4 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                >
            </div>

            <!-- Filtros em Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Estabelecimento -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estabelecimento</label>
                    <select 
                        name="establishment_id" 
                        id="establishment_id"
                        x-model="establishmentId"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                        <option value="">Todos os estabelecimentos</option>
                        @foreach($establishments as $establishment)
                            <option value="{{ $establishment->id }}">{{ $establishment->corporate_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Departamento -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Departamento</label>
                    <select 
                        name="department_id" 
                        id="department_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                        <option value="">Todos os departamentos</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select 
                        name="status"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>

                <!-- Dados Completos -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cadastro</label>
                    <select 
                        name="incomplete"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                        <option value="">Todos</option>
                        <option value="1" {{ request('incomplete') == '1' ? 'selected' : '' }}>Apenas Incompletos</option>
                        <option value="0" {{ request('incomplete') === '0' ? 'selected' : '' }}>Apenas Completos</option>
                    </select>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('employees.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Limpar Filtros
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
            </div>
        </form>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $employees->total() }}</p>
                </div>
                <i class="fas fa-users text-3xl text-blue-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Ativos</p>
                    <p class="text-2xl font-bold text-green-600">{{ $employees->where('status', 'active')->count() }}</p>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Inativos</p>
                    <p class="text-2xl font-bold text-red-600">{{ $employees->where('status', 'inactive')->count() }}</p>
                </div>
                <i class="fas fa-times-circle text-3xl text-red-500"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Incompletos</p>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ $employees->filter(function($emp) { return empty($emp->department_id) || empty($emp->position); })->count() }}
                    </p>
                </div>
                <i class="fas fa-exclamation-triangle text-3xl text-yellow-500"></i>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Nome Completo</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Matrícula</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">PIS</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Departamento</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Jornada</th>
                        <th class="text-center py-4 px-6 font-semibold text-gray-700">Status</th>
                        <th class="text-center py-4 px-6 font-semibold text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    @php
                        $isIncomplete = empty($employee->department_id) || empty($employee->position);
                    @endphp
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition {{ $isIncomplete ? 'bg-yellow-50' : '' }}">
                        <td class="py-4 px-6">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold mr-3">
                                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $employee->full_name }}</p>
                                    @if($isIncomplete)
                                        <p class="text-xs text-yellow-600">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Dados incompletos
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-gray-600">{{ $employee->matricula ?? '—' }}</td>
                        <td class="py-4 px-6 text-gray-600">{{ $employee->pis ?? '—' }}</td>
                        <td class="py-4 px-6 text-gray-600">{{ $employee->department->name ?? '—' }}</td>
                        <td class="py-4 px-6 text-gray-600">
                            @if($employee->workSchedules()->exists())
                                <span class="text-green-600"><i class="fas fa-check mr-1"></i>Configurada</span>
                            @else
                                <span class="text-gray-400"><i class="fas fa-minus mr-1"></i>Não configurada</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($employee->status === 'active')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle mr-1 text-xs"></i>Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-circle mr-1 text-xs"></i>Inativo
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('employees.show', $employee) }}" class="text-blue-600 hover:text-blue-800 transition" title="Ver Detalhes">
                                    <i class="fas fa-eye text-lg"></i>
                                </a>
                                <a href="{{ route('employees.edit', $employee) }}" class="text-yellow-600 hover:text-yellow-800 transition" title="Editar">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <a href="{{ route('employees.work-schedules.index', $employee) }}" class="text-purple-600 hover:text-purple-800 transition" title="Horários">
                                    <i class="fas fa-clock text-lg"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 px-6 text-center text-gray-500">
                            <i class="fas fa-users-slash text-5xl mb-4 text-gray-300"></i>
                            <p class="text-lg">Nenhum colaborador encontrado</p>
                            <p class="text-sm text-gray-400 mt-2">Tente ajustar os filtros ou adicione um novo colaborador</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($employees->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $employees->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Script para Filtros em Cascata -->
<script>
document.getElementById('establishment_id').addEventListener('change', function() {
    const establishmentId = this.value;
    const departmentSelect = document.getElementById('department_id');
    
    // Limpar departamentos
    departmentSelect.innerHTML = '<option value="">Todos os departamentos</option>';
    
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
