@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('work-shift-templates.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-users-cog text-blue-600 mr-3"></i>Aplicação em Massa de Jornadas
            </h1>
            <p class="text-gray-600 mt-2">Aplique uma jornada de trabalho a vários colaboradores de uma só vez</p>
        </div>
    </div>
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-600 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-600 text-xl mr-3"></i>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if(session('errors') && count(session('errors')) > 0)
    <div class="bg-yellow-50 border-l-4 border-yellow-600 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-3 mt-1"></i>
            <div>
                <p class="font-bold text-yellow-900 mb-2">Alguns erros ocorreram:</p>
                <ul class="list-disc list-inside text-sm text-yellow-800 space-y-1">
                    @foreach(session('errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('work-shift-templates.bulk-assign.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Seleção de Template -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-blue-600 font-bold">1</span>
                </div>
                Selecione a Jornada
            </h2>
            <select name="template_id" id="template_id" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                <option value="">-- Escolha uma jornada --</option>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}" data-type="{{ $template->type }}">
                        {{ $template->name }} ({{ $template->weekly_hours }}h/semana)
                        @if($template->is_preset) ⭐ @endif
                    </option>
                @endforeach
            </select>

            <div class="mt-4">
                <label class="block text-sm font-medium mb-2">Válido a partir de:</label>
                <input type="date" name="effective_from" value="{{ date('Y-m-d') }}" class="border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Seleção de Colaboradores -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-purple-600 font-bold">2</span>
                </div>
                Selecione os Colaboradores
            </h2>
            
            <!-- Filtros -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 pb-6 border-b">
                <div>
                    <label class="block text-sm font-medium mb-2">Filtrar por Estabelecimento:</label>
                    <select id="filter_establishment" class="w-full border rounded px-3 py-2">
                        <option value="">Todos</option>
                        @foreach($establishments as $est)
                            <option value="{{ $est->id }}">{{ $est->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Filtrar por Departamento:</label>
                    <select id="filter_department" class="w-full border rounded px-3 py-2">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <!-- Ações em Massa -->
            <div class="flex gap-3 mb-4">
                <button type="button" onclick="selectAll()" class="inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold rounded-lg transition">
                    <i class="fas fa-check-double mr-2"></i>Selecionar Todos
                </button>
                <button type="button" onclick="deselectAll()" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Desmarcar Todos
                </button>
                <span id="selected_count" class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg font-bold">
                    <i class="fas fa-users mr-2"></i>0 selecionados
                </span>
            </div>

            <!-- Lista de Colaboradores -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto border rounded p-4" id="employees_container">
                @php
                    $allEmployees = \App\Models\Employee::with(['establishment', 'department'])->orderBy('full_name')->get();
                @endphp
                
                @foreach($allEmployees as $emp)
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer employee-item" 
                           data-establishment="{{ $emp->establishment_id }}" 
                           data-department="{{ $emp->department_id }}">
                        <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="employee-checkbox" onchange="updateCount()">
                        <div class="flex-1">
                            <div class="font-medium">{{ $emp->full_name }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $emp->establishment->name ?? 'Sem estabelecimento' }}
                                @if($emp->department)
                                    - {{ $emp->department->name }}
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Botão de Envio -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-2xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Pronto para aplicar?</h3>
                        <p class="text-gray-600 text-sm">Esta ação substituirá os horários atuais dos colaboradores selecionados.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('work-shift-templates.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition">
                        <i class="fas fa-rocket mr-2"></i>Aplicar Jornada
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function updateCount() {
    const checked = document.querySelectorAll('.employee-checkbox:checked').length;
    document.getElementById('selected_count').textContent = checked + ' selecionado' + (checked !== 1 ? 's' : '');
}

function selectAll() {
    document.querySelectorAll('.employee-checkbox:not([disabled])').forEach(cb => {
        if (cb.closest('.employee-item').style.display !== 'none') {
            cb.checked = true;
        }
    });
    updateCount();
}

function deselectAll() {
    document.querySelectorAll('.employee-checkbox').forEach(cb => cb.checked = false);
    updateCount();
}

// Filtros
document.getElementById('filter_establishment').addEventListener('change', function() {
    const estId = this.value;
    const items = document.querySelectorAll('.employee-item');
    
    items.forEach(item => {
        if (!estId || item.dataset.establishment == estId) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
            item.querySelector('.employee-checkbox').checked = false;
        }
    });
    
    // Atualizar opções de departamento
    updateDepartmentFilter(estId);
    updateCount();
});

document.getElementById('filter_department').addEventListener('change', function() {
    const deptId = this.value;
    const estId = document.getElementById('filter_establishment').value;
    const items = document.querySelectorAll('.employee-item');
    
    items.forEach(item => {
        const matchEst = !estId || item.dataset.establishment == estId;
        const matchDept = !deptId || item.dataset.department == deptId;
        
        if (matchEst && matchDept) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
            item.querySelector('.employee-checkbox').checked = false;
        }
    });
    
    updateCount();
});

function updateDepartmentFilter(establishmentId) {
    const deptSelect = document.getElementById('filter_department');
    deptSelect.innerHTML = '<option value="">Todos</option>';
    
    if (!establishmentId) return;
    
    @foreach($establishments as $est)
        if ({{ $est->id }} == establishmentId) {
            @foreach($est->departments as $dept)
                deptSelect.innerHTML += '<option value="{{ $dept->id }}">{{ $dept->name }}</option>';
            @endforeach
        }
    @endforeach
}
</script>
@endsection
