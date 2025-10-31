@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-calendar-check text-blue-600 mr-3"></i>Cartão de Ponto
        </h1>
        <p class="text-gray-600 mt-2">Gere relatórios de ponto dos colaboradores com filtros inteligentes</p>
    </div>

    <div class="max-w-7xl mx-auto w-full">
        <!-- Painel de Filtros em Grid Horizontal -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                Filtros de Geração
            </h2>

            <form id="timesheetForm" action="{{ route('timesheets.generate') }}" method="POST">
                @csrf

                <!-- Grid: Linha 1 - Departamento e Colaboradores -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Departamento (2 colunas) -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-building text-blue-600 mr-2"></i>
                            <span class="text-blue-600">Passo 1:</span> Departamento *
                        </label>
                        <select 
                            id="department_id"
                            name="department_id" 
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('department_id') border-red-500 @enderror"
                        >
                            <option value="">Selecione um departamento</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }} - {{ $department->establishment->trade_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Primeiro selecione o departamento
                        </p>
                    </div>

                    <!-- Colaboradores (2 colunas) -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-users text-green-600 mr-2"></i>
                                <span class="text-green-600">Passo 2:</span> Colaboradores *
                            </label>
                            <div class="flex gap-2">
                                <button 
                                    type="button" 
                                    id="selectAllBtn" 
                                    class="text-xs text-blue-600 hover:text-blue-800 font-semibold hidden"
                                >
                                    <i class="fas fa-check-double mr-1"></i>Selecionar Todos
                                </button>
                                <button 
                                    type="button" 
                                    id="clearSelectionBtn" 
                                    class="text-xs text-red-600 hover:text-red-800 font-semibold hidden"
                                >
                                    <i class="fas fa-times-circle mr-1"></i>Limpar Seleção
                                </button>
                            </div>
                        </div>
                        <select 
                            id="employee_ids"
                            name="employee_ids[]" 
                            multiple
                            required
                            disabled
                            class="w-full @error('employee_ids') border-red-500 @enderror"
                        >
                            <option value="">Selecione o departamento primeiro</option>
                        </select>
                        @error('employee_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500" id="employeeHint">
                            <i class="fas fa-lock mr-1"></i>
                            Aguardando seleção do departamento...
                        </p>
                    </div>
                </div>

                <!-- Grid: Linha 2 - Datas e Botão -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-end">
                    <!-- Data Inicial (1 coluna) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                            Data Inicial *
                        </label>
                        <input 
                            type="date" 
                            name="start_date" 
                            id="start_date"
                            value="{{ old('start_date') }}"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('start_date') border-red-500 @enderror"
                        >
                        @error('start_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data Final (1 coluna) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-check text-purple-600 mr-2"></i>
                            Data Final *
                        </label>
                        <input 
                            type="date" 
                            name="end_date" 
                            id="end_date"
                            value="{{ old('end_date') }}"
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('end_date') border-red-500 @enderror"
                        >
                        @error('end_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Espaço Vazio (1 coluna) -->
                    <div class="hidden lg:block"></div>

                    <!-- Botão Gerar Relatório (1 coluna) -->
                    <div>
                        <button 
                            type="submit" 
                            id="generateBtn"
                            disabled
                            class="w-full font-semibold px-6 py-3 rounded-lg shadow-lg transition"
                            style="background-color: #9ca3af !important; color: white !important; cursor: not-allowed;"
                        >
                            <i class="fas fa-lock mr-2"></i>Preencha os campos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tom Select CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">

<!-- Tom Select JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department_id');
    const employeeSelect = document.getElementById('employee_ids');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const generateBtn = document.getElementById('generateBtn');
    const employeeHint = document.getElementById('employeeHint');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    let tomSelectInstance = null;

    // Inicializa Tom Select no campo de colaboradores
    function initTomSelect() {
        if (tomSelectInstance) {
            tomSelectInstance.destroy();
        }

        tomSelectInstance = new TomSelect('#employee_ids', {
            plugins: ['remove_button', 'clear_button'],
            maxItems: null,
            valueField: 'value',
            labelField: 'text',
            searchField: ['text', 'cpf', 'matricula'],
            placeholder: 'Digite para buscar colaboradores...',
            loadingClass: 'loading',
            render: {
                option: function(data, escape) {
                    return '<div class="py-2 px-3 hover:bg-blue-50 cursor-pointer">' +
                        '<div class="font-semibold text-gray-900">' + escape(data.text) + '</div>' +
                        '<div class="text-xs text-gray-500 mt-1">' +
                        '<span class="mr-3"><i class="fas fa-id-card mr-1"></i>CPF: ' + escape(data.cpf) + '</span>' +
                        '<span><i class="fas fa-hashtag mr-1"></i>Matrícula: ' + escape(data.matricula) + '</span>' +
                        '</div>' +
                        '</div>';
                },
                item: function(data, escape) {
                    return '<div class="px-2 py-1">' + escape(data.text) + '</div>';
                }
            },
            onInitialize: function() {
                this.disable();
            }
        });
    }

    // Carrega colaboradores quando departamento é selecionado
    departmentSelect.addEventListener('change', async function() {
        const departmentId = this.value;
        
        if (!departmentId) {
            tomSelectInstance.clearOptions();
            tomSelectInstance.clear();
            tomSelectInstance.disable();
            selectAllBtn.classList.add('hidden');
            clearSelectionBtn.classList.add('hidden');
            employeeHint.innerHTML = '<i class="fas fa-lock mr-1"></i>Aguardando seleção do departamento...';
            employeeHint.classList.remove('text-green-600');
            employeeHint.classList.add('text-gray-500');
            validateForm();
            return;
        }

        // Loading state
        employeeHint.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Carregando colaboradores...';
        employeeHint.classList.remove('text-gray-500');
        employeeHint.classList.add('text-blue-600');
        
        try {
            const response = await fetch(`/api/employees/by-department?department_id=${departmentId}`);
            const employees = await response.json();
            
            // Limpa e popula o Tom Select
            tomSelectInstance.clearOptions();
            tomSelectInstance.clear();
            
            if (employees.length === 0) {
                employeeHint.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Nenhum colaborador ativo neste departamento';
                employeeHint.classList.remove('text-blue-600');
                employeeHint.classList.add('text-yellow-600');
                tomSelectInstance.disable();
                selectAllBtn.classList.add('hidden');
                clearSelectionBtn.classList.add('hidden');
            } else {
                tomSelectInstance.addOptions(employees);
                tomSelectInstance.enable();
                selectAllBtn.classList.remove('hidden');
                clearSelectionBtn.classList.remove('hidden');
                
                employeeHint.innerHTML = `<i class="fas fa-check-circle mr-1"></i>${employees.length} colaborador(es) disponível(eis). Use a busca ou "Selecionar Todos"`;
                employeeHint.classList.remove('text-blue-600');
                employeeHint.classList.add('text-green-600');
            }
        } catch (error) {
            console.error('Erro ao carregar colaboradores:', error);
            employeeHint.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Erro ao carregar colaboradores';
            employeeHint.classList.remove('text-blue-600');
            employeeHint.classList.add('text-red-600');
        }
        
        validateForm();
    });

    // Botão "Selecionar Todos"
    selectAllBtn.addEventListener('click', function() {
        const allValues = Object.keys(tomSelectInstance.options);
        tomSelectInstance.setValue(allValues);
        validateForm();
    });

    // Botão "Limpar Seleção"
    clearSelectionBtn.addEventListener('click', function() {
        if (tomSelectInstance) {
            // Força a limpeza e atualização visual
            tomSelectInstance.setValue([]);
            tomSelectInstance.blur();
        }
        validateForm();
    });

    // Valida formulário em tempo real
    function validateForm() {
        const hasEmployee = tomSelectInstance && tomSelectInstance.items.length > 0;
        const hasDepartment = departmentSelect.value !== '';
        const hasStartDate = startDate.value !== '';
        const hasEndDate = endDate.value !== '';
        
        const isValid = hasDepartment && hasEmployee && hasStartDate && hasEndDate;
        
        if (isValid) {
            generateBtn.disabled = false;
            generateBtn.style.backgroundColor = '#2563eb';
            generateBtn.style.color = 'white';
            generateBtn.style.cursor = 'pointer';
            generateBtn.innerHTML = '<i class="fas fa-file-pdf mr-2"></i>Gerar Relatório (' + tomSelectInstance.items.length + ' colaborador' + (tomSelectInstance.items.length > 1 ? 'es' : '') + ')';
            
            // Adiciona hover effect
            generateBtn.onmouseenter = function() {
                this.style.backgroundColor = '#1d4ed8';
            };
            generateBtn.onmouseleave = function() {
                if (!this.disabled) {
                    this.style.backgroundColor = '#2563eb';
                }
            };
        } else {
            generateBtn.disabled = true;
            generateBtn.style.backgroundColor = '#9ca3af';
            generateBtn.style.color = 'white';
            generateBtn.style.cursor = 'not-allowed';
            generateBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Preencha todos os campos';
            
            // Remove hover effect
            generateBtn.onmouseenter = null;
            generateBtn.onmouseleave = null;
        }
    }

    // Listeners para validação
    if (tomSelectInstance) {
        tomSelectInstance.on('change', validateForm);
    }
    startDate.addEventListener('change', validateForm);
    endDate.addEventListener('change', validateForm);

    // Inicializa o Tom Select
    initTomSelect();
});
</script>

@endsection
