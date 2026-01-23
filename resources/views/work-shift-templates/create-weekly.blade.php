@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('work-shift-templates.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-plus-circle text-blue-600 mr-3"></i>Novo Modelo de Jornada
            </h1>
            <p class="text-gray-600 mt-2">Crie um template de jornada para aplicar a múltiplos colaboradores</p>
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
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-tag text-blue-600 mr-1"></i>Nome do Modelo *
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror" placeholder="Ex: Comercial Personalizado">
                @error('name')
                    <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-clock text-blue-600 mr-1"></i>Carga Horária Semanal *
                </label>
                <input type="number" name="weekly_hours" value="{{ old('weekly_hours', 40) }}" step="0.5" min="0" max="168" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('weekly_hours') border-red-500 @enderror" placeholder="40">
                @error('weekly_hours')
                    <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                @enderror
            </div>
        </div>

        <input type="hidden" name="type" value="weekly">

        <h3 class="text-lg font-bold text-gray-900 mb-6 pb-3 border-b-2 border-gray-200 flex items-center">
            <i class="fas fa-calendar-week text-blue-600 mr-2"></i>Horários da Semana
        </h3>
        
        <div class="space-y-4" id="schedules-container">
            @php
                $days = [
                    1 => 'Segunda-feira',
                    2 => 'Terça-feira',
                    3 => 'Quarta-feira',
                    4 => 'Quinta-feira',
                    5 => 'Sexta-feira',
                    6 => 'Sábado',
                    0 => 'Domingo',
                ];
            @endphp

            @foreach($days as $dayNum => $dayName)
            <div class="border rounded-lg p-4 bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <label class="font-semibold text-gray-700">{{ $dayName }}</label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="schedules[{{ $dayNum }}][is_work_day]" value="1" checked 
                               onchange="toggleDayInputs({{ $dayNum }})" id="is_work_day_{{ $dayNum }}" class="rounded">
                        <span class="text-sm">Dia de trabalho</span>
                    </label>
                </div>
                
                <input type="hidden" name="schedules[{{ $dayNum }}][day_of_week]" value="{{ $dayNum }}">
                
                <div id="day_inputs_{{ $dayNum }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Entrada 1</label>
                        <input type="time" name="schedules[{{ $dayNum }}][entry_1]" class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Saída 1</label>
                        <input type="time" name="schedules[{{ $dayNum }}][exit_1]" class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Entrada 2</label>
                        <input type="time" name="schedules[{{ $dayNum }}][entry_2]" class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Saída 2</label>
                        <input type="time" name="schedules[{{ $dayNum }}][exit_2]" class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t-2 border-gray-200">
            <a href="{{ route('work-shift-templates.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                <i class="fas fa-save mr-2"></i>Salvar Modelo
            </button>
        </div>
    </form>
</div>

<script>
function toggleDayInputs(dayNum) {
    const checkbox = document.getElementById('is_work_day_' + dayNum);
    const inputs = document.getElementById('day_inputs_' + dayNum);
    
    if (checkbox.checked) {
        inputs.classList.remove('opacity-50', 'pointer-events-none');
    } else {
        inputs.classList.add('opacity-50', 'pointer-events-none');
        // Limpar inputs
        inputs.querySelectorAll('input').forEach(input => input.value = '');
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    [1, 2, 3, 4, 5, 6, 0].forEach(day => {
        toggleDayInputs(day);
    });
});
</script>
@endsection
