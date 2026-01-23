@extends('layouts.main')

@section('content')
<div class="mb-6">
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

    <div class="flex items-center mb-6">
        <a href="{{ route('work-shift-templates.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-users-cog text-blue-600 mr-3"></i>Aplicação em Massa de Jornadas
            </h1>
            <p class="text-gray-600 mt-2">Aplique uma jornada de trabalho a vários vínculos (matrículas) de uma só vez</p>
        </div>
    </div>

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
                        {{ $template->name }} 
                        @if($template->type === 'weekly')
                            ({{ $template->weekly_hours }}h/semana)
                        @elseif($template->type === 'rotating_shift')
                            (Escala {{ $template->rotatingRule->work_days }}x{{ $template->rotatingRule->rest_days }})
                        @elseif($template->type === 'weekly_hours')
                            ({{ $template->flexibleHours->weekly_hours_required }}h flexíveis)
                        @endif
                        @if($template->is_preset) ⭐ @endif
                    </option>
                @endforeach
            </select>

            <div class="mt-4">
                <label class="block text-sm font-medium mb-2">Válido a partir de:</label>
                <input type="date" name="effective_from" value="{{ date('Y-m-d') }}" class="border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Seleção de Vínculos -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-purple-600 font-bold">2</span>
                </div>
                Selecione os Vínculos (Matrículas)
            </h2>
            
            <!-- Filtros -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b">
                <div>
                    <label class="block text-sm font-medium mb-2">Filtrar por Estabelecimento:</label>
                    <select id="filter_establishment" class="w-full border rounded px-3 py-2">
                        <option value="">Todos</option>
                        @foreach($establishments as $est)
                            <option value="{{ $est->id }}">{{ $est->name ?: $est->corporate_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Filtrar por Departamento:</label>
                    <select id="filter_department" class="w-full border rounded px-3 py-2">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Filtrar por Status de Jornada:</label>
                    <select id="filter_workshift" class="w-full border rounded px-3 py-2">
                        <option value="">Todos</option>
                        <option value="with">Com jornada atribuída</option>
                        <option value="without">Sem jornada atribuída</option>
                    </select>
                </div>
            </div>

            <!-- Ações em Massa -->
            <div class="flex gap-3 mb-4">
                <button type="button" onclick="selectAll()" class="inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold rounded-lg transition">
                    <i class="fas fa-check-double mr-2"></i>Selecionar Todos Visíveis
                </button>
                <button type="button" onclick="deselectAll()" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Desmarcar Todos
                </button>
                <span id="selected_count" class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg font-bold">
                    <i class="fas fa-id-card mr-2"></i>0 selecionados
                </span>
            </div>

            <!-- Lista de Vínculos -->
            @if($registrations->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-exclamation-circle text-4xl text-gray-400 mb-3"></i>
                    <p class="text-lg">Nenhum vínculo ativo encontrado.</p>
                    <p class="text-sm mt-2">Crie vínculos para os colaboradores antes de atribuir jornadas.</p>
                </div>
            @else
                <div class="space-y-2 max-h-96 overflow-y-auto border rounded p-4" id="registrations_container">
                    @foreach($registrations as $reg)
                        <label class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer border border-gray-200 registration-item" 
                               data-establishment="{{ $reg->establishment_id }}" 
                               data-department="{{ $reg->department_id }}"
                               data-has-workshift="{{ $reg->currentWorkShiftAssignment ? '1' : '0' }}">
                            <input type="checkbox" 
                                   name="registration_ids[]" 
                                   value="{{ $reg->id }}" 
                                   class="registration-checkbox w-5 h-5 text-blue-600" 
                                   onchange="updateCount()">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-2 items-center">
                                <!-- Matrícula e Nome -->
                                <div class="md:col-span-4">
                                    <div class="font-bold text-gray-900">
                                        <i class="fas fa-id-card text-blue-600 mr-1"></i>
                                        {{ $reg->matricula }}
                                    </div>
                                    <div class="text-sm text-gray-700 mt-1">
                                        <i class="fas fa-user text-gray-400 mr-1"></i>
                                        {{ $reg->person->full_name }}
                                    </div>
                                </div>

                                <!-- Função -->
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-500">Função</div>
                                    <div class="text-sm font-medium text-gray-800">
                                        {{ $reg->position ?: 'N/A' }}
                                    </div>
                                </div>

                                <!-- Estabelecimento -->
                                <div class="md:col-span-3">
                                    <div class="text-xs text-gray-500">Estabelecimento</div>
                                    <div class="text-sm font-medium text-gray-800">
                                        {{ $reg->establishment ? ($reg->establishment->name ?: $reg->establishment->corporate_name) : 'N/A' }}
                                    </div>
                                </div>

                                <!-- Departamento -->
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-500">Departamento</div>
                                    <div class="text-sm font-medium text-gray-800">
                                        {{ $reg->department->name ?? 'N/A' }}
                                    </div>
                                </div>

                                <!-- Jornada Atual -->
                                <div class="md:col-span-1 text-right">
                                    @if($reg->currentWorkShiftAssignment)
                                        <span class="inline-flex items-center bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full" title="{{ $reg->currentWorkShiftAssignment->template->name }}">
                                            <i class="fas fa-clock mr-1"></i>
                                            Sim
                                        </span>
                                    @else
                                        <span class="inline-flex items-center bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded-full">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Não
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Botão de Envio -->
        @if($registrations->isNotEmpty())
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-2xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Pronto para aplicar?</h3>
                        <p class="text-gray-600 text-sm">Esta ação substituirá as jornadas atuais dos vínculos selecionados.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('work-shift-templates.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition" id="submitBtn" disabled>
                        <i class="fas fa-rocket mr-2"></i>Aplicar Jornada
                    </button>
                </div>
            </div>
        </div>
        @endif
    </form>
</div>

<script>
function updateCount() {
    const checked = document.querySelectorAll('.registration-checkbox:checked').length;
    document.getElementById('selected_count').innerHTML = `<i class="fas fa-id-card mr-2"></i>${checked} selecionado${checked !== 1 ? 's' : ''}`;
    
    // Habilitar/desabilitar botão de submit
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.disabled = checked === 0;
    }
}

function selectAll() {
    document.querySelectorAll('.registration-checkbox').forEach(cb => {
        const item = cb.closest('.registration-item');
        if (item && item.style.display !== 'none') {
            cb.checked = true;
        }
    });
    updateCount();
}

function deselectAll() {
    document.querySelectorAll('.registration-checkbox').forEach(cb => cb.checked = false);
    updateCount();
}

function applyFilters() {
    const estId = document.getElementById('filter_establishment').value;
    const deptId = document.getElementById('filter_department').value;
    const workshiftFilter = document.getElementById('filter_workshift').value;
    const items = document.querySelectorAll('.registration-item');
    
    items.forEach(item => {
        const matchEst = !estId || item.dataset.establishment == estId;
        const matchDept = !deptId || item.dataset.department == deptId;
        const hasWorkshift = item.dataset.hasWorkshift == '1';
        const matchWorkshift = !workshiftFilter || 
                              (workshiftFilter === 'with' && hasWorkshift) ||
                              (workshiftFilter === 'without' && !hasWorkshift);
        
        if (matchEst && matchDept && matchWorkshift) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
            item.querySelector('.registration-checkbox').checked = false;
        }
    });
    
    updateCount();
}

// Event listeners para filtros
document.getElementById('filter_establishment').addEventListener('change', function() {
    const estId = this.value;
    updateDepartmentFilter(estId);
    applyFilters();
});

document.getElementById('filter_department').addEventListener('change', applyFilters);
document.getElementById('filter_workshift').addEventListener('change', applyFilters);

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
