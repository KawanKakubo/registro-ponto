@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">➕ Novo Modelo de Jornada</h1>
    
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <form action="{{ route('work-shift-templates.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block font-medium mb-2">Nome do Modelo *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" placeholder="Ex: Comercial Personalizado">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block font-medium mb-2">Carga Horária Semanal *</label>
                <input type="number" name="weekly_hours" value="{{ old('weekly_hours', 40) }}" step="0.5" min="0" max="168" required class="w-full border rounded px-3 py-2 @error('weekly_hours') border-red-500 @enderror" placeholder="40">
                @error('weekly_hours')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <input type="hidden" name="type" value="weekly">

        <h3 class="text-lg font-bold mb-4 pb-2 border-b">Horários da Semana</h3>
        
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

        <div class="flex gap-4 mt-6 pt-6 border-t">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 font-bold">💾 Salvar Modelo</button>
            <a href="{{ route('work-shift-templates.index') }}" class="bg-gray-300 px-6 py-2 rounded hover:bg-gray-400">Cancelar</a>
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
