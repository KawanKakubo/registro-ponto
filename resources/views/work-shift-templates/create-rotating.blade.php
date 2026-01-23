@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('work-shift-templates.select-type') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-sync-alt text-purple-600 mr-3"></i>Criar Escala de Revezamento
            </h1>
            <p class="text-gray-600 mt-2">Configure um modelo de plantão com revezamento cíclico (12x36, 24x72, etc)</p>
        </div>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-600 text-xl mr-3"></i>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <form action="{{ route('work-shift-templates.store') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
        @csrf
        <input type="hidden" name="type" value="rotating_shift">
        
        <!-- Informações Básicas -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">
                <i class="fas fa-info-circle text-purple-600 mr-2"></i>Informações Básicas
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-tag text-purple-600 mr-1"></i>Nome do Modelo *
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('name') border-red-500 @enderror" 
                           placeholder="Ex: Plantão 12x36 - Hospital">
                    @error('name')
                        <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-align-left text-purple-600 mr-1"></i>Descrição (Opcional)
                    </label>
                    <input type="text" 
                           name="description" 
                           value="{{ old('description') }}" 
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('description') border-red-500 @enderror" 
                           placeholder="Ex: Escala para enfermeiros e médicos">
                    @error('description')
                        <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Configuração do Ciclo -->
        <div class="mb-8 bg-purple-50 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-sync text-purple-600 mr-2"></i>Configuração do Ciclo de Revezamento
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Dias de Trabalho *
                    </label>
                    <input type="number" 
                           name="work_days" 
                           id="work_days"
                           value="{{ old('work_days', 1) }}" 
                           min="1" 
                           max="30"
                           required 
                           onchange="updateCycleInfo()"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('work_days') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Quantos dias consecutivos de trabalho</p>
                    @error('work_days')
                        <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Dias de Descanso *
                    </label>
                    <input type="number" 
                           name="rest_days" 
                           id="rest_days"
                           value="{{ old('rest_days', 2) }}" 
                           min="1" 
                           max="30"
                           required 
                           onchange="updateCycleInfo()"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('rest_days') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Quantos dias consecutivos de folga</p>
                    @error('rest_days')
                        <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Ciclo Completo
                    </label>
                    <div class="bg-white border-2 border-purple-300 rounded-lg px-4 py-3">
                        <p class="text-2xl font-bold text-purple-600" id="cycle_total">3 dias</p>
                        <p class="text-xs text-gray-500 mt-1">Total do ciclo</p>
                    </div>
                </div>
            </div>
            
            <!-- Exemplos Comuns -->
            <div class="bg-white rounded-lg p-4 border-2 border-purple-200">
                <p class="text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>Escalas Comuns:
                </p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setScale(1, 2)" class="px-3 py-1 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded text-sm font-medium transition">
                        12x36 (1 trab + 2 folga)
                    </button>
                    <button type="button" onclick="setScale(1, 3)" class="px-3 py-1 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded text-sm font-medium transition">
                        24x72 (1 trab + 3 folga)
                    </button>
                    <button type="button" onclick="setScale(2, 2)" class="px-3 py-1 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded text-sm font-medium transition">
                        24x48 (2 trab + 2 folga)
                    </button>
                    <button type="button" onclick="setScale(6, 1)" class="px-3 py-1 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded text-sm font-medium transition">
                        6x1 (6 trab + 1 folga)
                    </button>
                </div>
            </div>
        </div>

        <!-- Horário do Plantão -->
        <div class="mb-8 bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-clock text-blue-600 mr-2"></i>Horário do Plantão
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Horário de Início *
                    </label>
                    <input type="time" 
                           name="shift_start_time" 
                           id="shift_start_time"
                           value="{{ old('shift_start_time', '07:00') }}" 
                           required 
                           onchange="calculateDuration()"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('shift_start_time') border-red-500 @enderror">
                    @error('shift_start_time')
                        <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Horário de Término *
                    </label>
                    <input type="time" 
                           name="shift_end_time" 
                           id="shift_end_time"
                           value="{{ old('shift_end_time', '19:00') }}" 
                           required 
                           onchange="calculateDuration()"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('shift_end_time') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Se o plantão cruza meia-noite, será calculado corretamente</p>
                    @error('shift_end_time')
                        <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Duração Calculada
                    </label>
                    <div class="bg-white border-2 border-blue-300 rounded-lg px-4 py-3">
                        <p class="text-2xl font-bold text-blue-600" id="duration_display">12 horas</p>
                        <p class="text-xs text-gray-500 mt-1">Duração do turno</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regras de Validação -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>Regras de Validação
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-start">
                    <input type="checkbox" 
                           name="validate_exact_hours" 
                           id="validate_exact_hours"
                           value="1" 
                           checked
                           onchange="toggleToleranceField()"
                           class="mt-1 h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <div class="ml-3">
                        <label for="validate_exact_hours" class="font-semibold text-gray-700">
                            Validar horário exato de entrada/saída
                        </label>
                        <p class="text-sm text-gray-600">Se marcado, o sistema verificará se o colaborador cumpriu as horas exatas do plantão</p>
                    </div>
                </div>
                
                <div id="tolerance_field" class="ml-8 pl-4 border-l-4 border-purple-300">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Tolerância (minutos)
                    </label>
                    <input type="number" 
                           name="tolerance_minutes" 
                           value="{{ old('tolerance_minutes', 15) }}" 
                           min="0" 
                           max="120"
                           class="w-48 border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <p class="text-xs text-gray-500 mt-1">Quantos minutos de diferença são aceitáveis</p>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex items-center justify-between pt-6 border-t-2 border-gray-200">
            <a href="{{ route('work-shift-templates.select-type') }}" 
               class="px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" 
                    class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-lg transition">
                <i class="fas fa-save mr-2"></i>Criar Modelo de Escala
            </button>
        </div>
    </form>
</div>

<script>
function updateCycleInfo() {
    const workDays = parseInt(document.getElementById('work_days').value) || 0;
    const restDays = parseInt(document.getElementById('rest_days').value) || 0;
    const total = workDays + restDays;
    
    document.getElementById('cycle_total').textContent = total + ' dias';
}

function setScale(work, rest) {
    document.getElementById('work_days').value = work;
    document.getElementById('rest_days').value = rest;
    updateCycleInfo();
}

function calculateDuration() {
    const startTime = document.getElementById('shift_start_time').value;
    const endTime = document.getElementById('shift_end_time').value;
    
    if (!startTime || !endTime) return;
    
    const [startHour, startMin] = startTime.split(':').map(Number);
    const [endHour, endMin] = endTime.split(':').map(Number);
    
    let startMinutes = startHour * 60 + startMin;
    let endMinutes = endHour * 60 + endMin;
    
    // Se o fim é antes do início, o plantão cruza a meia-noite
    if (endMinutes < startMinutes) {
        endMinutes += 24 * 60;
    }
    
    const durationMinutes = endMinutes - startMinutes;
    const hours = Math.floor(durationMinutes / 60);
    const minutes = durationMinutes % 60;
    
    document.getElementById('duration_display').textContent = 
        hours + 'h' + (minutes > 0 ? minutes + 'min' : '');
}

function toggleToleranceField() {
    const checkbox = document.getElementById('validate_exact_hours');
    const field = document.getElementById('tolerance_field');
    
    if (checkbox.checked) {
        field.style.display = 'block';
    } else {
        field.style.display = 'none';
    }
}

// Inicializar valores ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    updateCycleInfo();
    calculateDuration();
    toggleToleranceField();
});
</script>
@endsection
