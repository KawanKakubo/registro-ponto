@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-calendar-check text-blue-600 mr-3"></i>Cartão de Ponto
        </h1>
        <p class="text-gray-600 mt-2">Gere relatórios de ponto dos colaboradores com filtros inteligentes</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Painel de Filtros em Cascata (Esquerda) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-filter text-blue-600 mr-2"></i>
                    Filtros de Geração
                </h2>

                <form id="timesheetForm" action="{{ route('timesheets.generate') }}" method="POST">
                    @csrf

                    <!-- Passo 1: Departamento (Obrigatório Primeiro) -->
                    <div class="mb-6">
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
                            Primeiro selecione o departamento para filtrar os colaboradores
                        </p>
                    </div>

                    <!-- Passo 2: Colaboradores (Multi-select com Busca) -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-users text-green-600 mr-2"></i>
                                <span class="text-green-600">Passo 2:</span> Colaboradores *
                            </label>
                            <button 
                                type="button" 
                                id="selectAllBtn" 
                                class="text-xs text-blue-600 hover:text-blue-800 font-semibold hidden"
                            >
                                <i class="fas fa-check-double mr-1"></i>Selecionar Todos
                            </button>
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

                    <!-- Passo 3: Período -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                            <span class="text-purple-600">Passo 3:</span> Período do Relatório *
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Data Inicial
                                </label>
                                <input 
                                    type="date" 
                                    name="start_date" 
                                    id="start_date"
                                    value="{{ old('start_date') }}"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('start_date') border-red-500 @enderror"
                                >
                                @error('start_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Data Final
                                </label>
                                <input 
                                    type="date" 
                                    name="end_date" 
                                    id="end_date"
                                    value="{{ old('end_date') }}"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('end_date') border-red-500 @enderror"
                                >
                                @error('end_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botão de Geração (Smart Enable/Disable) -->
                    <button 
                        type="submit" 
                        id="generateBtn"
                        disabled
                        class="w-full bg-gray-400 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition cursor-not-allowed"
                    >
                        <i class="fas fa-lock mr-2"></i>Preencha todos os campos
                    </button>
                </form>
            </div>

            <!-- Info Card com Novo Fluxo -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white border-l-4 border-blue-700 rounded-lg p-4 mt-6 shadow-lg">
                <div class="flex items-start">
                    <i class="fas fa-lightbulb text-yellow-300 text-2xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="font-bold text-white mb-3">Fluxo Otimizado</h3>
                        <ol class="text-sm space-y-2">
                            <li class="flex items-start">
                                <span class="bg-white/20 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold mr-2 flex-shrink-0 mt-0.5">1</span>
                                <span>Escolha o <strong>departamento</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-white/20 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold mr-2 flex-shrink-0 mt-0.5">2</span>
                                <span>Selecione <strong>colaboradores</strong> específicos ou use "Selecionar Todos"</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-white/20 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold mr-2 flex-shrink-0 mt-0.5">3</span>
                                <span>Defina o <strong>período</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-white/20 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold mr-2 flex-shrink-0 mt-0.5">4</span>
                                <span>Clique em <strong>"Gerar Relatório"</strong></span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Área de Visualização (Direita) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center py-12">
                    <i class="fas fa-file-invoice text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Nenhum relatório gerado</h3>
                    <p class="text-gray-600 mb-6">Utilize os filtros em cascata para gerar cartões de ponto de forma eficiente</p>
                    
                    <!-- Features -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8 text-left">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="bg-blue-600 rounded-full p-2 mr-3">
                                    <i class="fas fa-filter text-white text-sm"></i>
                                </div>
                                <h4 class="font-bold text-blue-900">Filtragem Inteligente</h4>
                            </div>
                            <p class="text-sm text-blue-800">Sistema de cascata que guia você passo a passo na seleção</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="bg-green-600 rounded-full p-2 mr-3">
                                    <i class="fas fa-users text-white text-sm"></i>
                                </div>
                                <h4 class="font-bold text-green-900">Multi-Seleção</h4>
                            </div>
                            <p class="text-sm text-green-800">Selecione múltiplos colaboradores ou todo o departamento de uma vez</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="bg-purple-600 rounded-full p-2 mr-3">
                                    <i class="fas fa-search text-white text-sm"></i>
                                </div>
                                <h4 class="font-bold text-purple-900">Busca Rápida</h4>
                            </div>
                            <p class="text-sm text-purple-800">Digite para encontrar colaboradores instantaneamente entre 438 funcionários</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="bg-orange-600 rounded-full p-2 mr-3">
                                    <i class="fas fa-file-pdf text-white text-sm"></i>
                                </div>
                                <h4 class="font-bold text-orange-900">PDFs Profissionais</h4>
                            </div>
                            <p class="text-sm text-orange-800">Relatórios completos conforme Portaria 671/2021 do MTP</p>
                        </div>
                    </div>

                    <!-- Status Indicator -->
                    <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center justify-center text-yellow-800">
                            <i class="fas fa-arrow-left text-yellow-600 mr-3 animate-pulse"></i>
                            <span class="text-sm font-medium">Comece preenchendo o filtro de <strong>Departamento</strong></span>
                        </div>
                    </div>
                </div>
            </div>
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
            } else {
                tomSelectInstance.addOptions(employees);
                tomSelectInstance.enable();
                selectAllBtn.classList.remove('hidden');
                
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

    // Valida formulário em tempo real
    function validateForm() {
        const hasEmployee = tomSelectInstance && tomSelectInstance.items.length > 0;
        const hasDepartment = departmentSelect.value !== '';
        const hasStartDate = startDate.value !== '';
        const hasEndDate = endDate.value !== '';
        
        const isValid = hasDepartment && hasEmployee && hasStartDate && hasEndDate;
        
        if (isValid) {
            generateBtn.disabled = false;
            generateBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            generateBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
            generateBtn.innerHTML = '<i class="fas fa-file-pdf mr-2"></i>Gerar Relatório (' + tomSelectInstance.items.length + ' colaborador' + (tomSelectInstance.items.length > 1 ? 'es' : '') + ')';
        } else {
            generateBtn.disabled = true;
            generateBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
            generateBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            generateBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Preencha todos os campos';
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
