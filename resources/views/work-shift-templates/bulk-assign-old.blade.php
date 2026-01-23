@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold mb-2">🚀 Aplicação em Massa de Jornadas</h1>
    <p class="text-gray-600 mb-6">Aplique uma jornada de trabalho a vários colaboradores de uma só vez</p>
    
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('errors') && count(session('errors')) > 0)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-4">
            <p class="font-bold mb-2">⚠️ Alguns erros ocorreram:</p>
            <ul class="list-disc list-inside text-sm">
                @foreach(session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('work-shift-templates.bulk-assign.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Seleção de Template -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">1️⃣ Selecione a Jornada</h2>
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
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">2️⃣ Selecione os Colaboradores</h2>
            
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
                <button type="button" onclick="selectAll()" class="bg-blue-100 text-blue-700 px-4 py-2 rounded hover:bg-blue-200">
                    ✅ Selecionar Todos
                </button>
                <button type="button" onclick="deselectAll()" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">
                    ❌ Desmarcar Todos
                </button>
                <span id="selected_count" class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded font-bold">
                    0 selecionados
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
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Pronto para aplicar?</h3>
                    <p class="text-gray-600 text-sm">Esta ação substituirá os horários atuais dos colaboradores selecionados.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('work-shift-templates.index') }}" class="bg-gray-300 px-6 py-3 rounded-lg hover:bg-gray-400">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold px-8 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 shadow-md hover:shadow-lg transition-all">
                        🚀 Aplicar Jornada
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
