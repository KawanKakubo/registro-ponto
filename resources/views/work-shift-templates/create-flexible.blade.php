@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('work-shift-templates.select-type') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-clock text-green-600 mr-3"></i>Criar Jornada por Carga Horária
            </h1>
            <p class="text-gray-600 mt-2">Configure um modelo com carga horária flexível (ideal para professores)</p>
        </div>
    </div>

    <form action="{{ route('work-shift-templates.store') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
        @csrf
        <input type="hidden" name="type" value="weekly_hours">
        
        <!-- Informações Básicas -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">
                <i class="fas fa-info-circle text-green-600 mr-2"></i>Informações Básicas
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-tag text-green-600 mr-1"></i>Nome do Modelo *
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                           placeholder="Ex: Professor 20h">
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-align-left text-green-600 mr-1"></i>Descrição (Opcional)
                    </label>
                    <input type="text" name="description" value="{{ old('description') }}" 
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                           placeholder="Ex: Carga horária flexível para docentes">
                </div>
            </div>
        </div>

        <!-- Configuração da Carga Horária -->
        <div class="mb-8 bg-green-50 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-hourglass-half text-green-600 mr-2"></i>Configuração da Carga Horária
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Carga Horária Semanal *
                    </label>
                    <div class="flex items-center">
                        <input type="number" name="weekly_hours_required" value="{{ old('weekly_hours_required', 20) }}" 
                               min="1" max="168" step="0.5" required 
                               class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <span class="ml-2 text-gray-600 font-semibold">horas/semana</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Total de horas que o colaborador deve cumprir por semana</p>
                    @error('weekly_hours_required')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Período de Apuração *
                    </label>
                    <select name="period_type" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="weekly" selected>Semanal (segunda a domingo)</option>
                        <option value="biweekly">Quinzenal (14 dias)</option>
                        <option value="monthly">Mensal</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Período em que as horas serão somadas e validadas</p>
                </div>
            </div>
            
            <!-- Exemplos Comuns -->
            <div class="mt-4 bg-white rounded-lg p-4 border-2 border-green-200">
                <p class="text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>Cargas Horárias Comuns:
                </p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="document.querySelector('[name=weekly_hours_required]').value=20" 
                            class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm font-medium">20h</button>
                    <button type="button" onclick="document.querySelector('[name=weekly_hours_required]').value=25" 
                            class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm font-medium">25h</button>
                    <button type="button" onclick="document.querySelector('[name=weekly_hours_required]').value=30" 
                            class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm font-medium">30h</button>
                    <button type="button" onclick="document.querySelector('[name=weekly_hours_required]').value=40" 
                            class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm font-medium">40h</button>
                </div>
            </div>
        </div>

        <!-- Regras de Controle -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-gray-200">
                <i class="fas fa-sliders-h text-orange-600 mr-2"></i>Regras de Controle (Opcional)
            </h3>
            
            <div class="space-y-6">
                <div class="flex items-start">
                    <input type="checkbox" name="requires_minimum_daily_hours" id="req_daily" value="1" 
                           onchange="toggleMinDaily()"
                           class="mt-1 h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <div class="ml-3 flex-1">
                        <label for="req_daily" class="font-semibold text-gray-700">
                            Exigir mínimo de horas por dia trabalhado
                        </label>
                        <p class="text-sm text-gray-600">Se marcado, dias com menos horas que o mínimo serão sinalizados</p>
                        
                        <div id="min_daily_field" style="display: none;" class="mt-3 ml-4 pl-4 border-l-4 border-green-300">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Mínimo de horas por dia</label>
                            <input type="number" name="minimum_daily_hours" value="4" min="0.5" max="24" step="0.5"
                                   class="w-48 border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i>
                        Tolerância para considerar ausência
                    </label>
                    <div class="flex items-center">
                        <input type="number" name="grace_minutes" value="{{ old('grace_minutes', 15) }}" 
                               min="0" max="120"
                               class="w-32 border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500">
                        <span class="ml-2 text-gray-600">minutos</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Se o colaborador trabalhar menos que isso em um dia, o dia será ignorado</p>
                </div>
            </div>
        </div>

        <!-- Informativo -->
        <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                <div>
                    <p class="text-blue-900 font-semibold mb-1">Como funciona este modelo:</p>
                    <p class="text-blue-800 text-sm">
                        Neste modelo, o sistema <strong>não valida horários fixos</strong>. Ele irá somar todas as horas 
                        trabalhadas no período configurado e comparar com a carga horária devida. O colaborador tem flexibilidade 
                        para trabalhar em horários diferentes, desde que cumpra o total de horas no período.
                    </p>
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
                    class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg transition">
                <i class="fas fa-save mr-2"></i>Criar Modelo de Carga Horária
            </button>
        </div>
    </form>
</div>

<script>
function toggleMinDaily() {
    const checkbox = document.getElementById('req_daily');
    const field = document.getElementById('min_daily_field');
    field.style.display = checkbox.checked ? 'block' : 'none';
}
</script>
@endsection
