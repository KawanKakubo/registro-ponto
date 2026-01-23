@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('work-shift-templates.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-edit text-blue-600 mr-3"></i>Editar Modelo de Jornada
            </h1>
            <p class="text-gray-600 mt-2">{{ $template->name }}</p>
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

    <form action="{{ route('work-shift-templates.update', $template) }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-tag text-blue-600 mr-1"></i>Nome do Modelo *
                </label>
                <input type="text" name="name" value="{{ old('name', $template->name) }}" required 
                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-600 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-list text-blue-600 mr-1"></i>Tipo de Jornada
                </label>
                <input type="text" value="{{ $template->type_formatted }}" disabled 
                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 bg-gray-100">
                <p class="text-xs text-gray-500 mt-1">O tipo não pode ser alterado após criação</p>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                <i class="fas fa-info-circle text-blue-600 mr-1"></i>Descrição
            </label>
            <textarea name="description" rows="3" 
                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $template->description) }}</textarea>
        </div>

        <input type="hidden" name="type" value="{{ $template->type }}">

        @if($template->type === 'weekly')
            {{-- Formulário para Jornada Semanal --}}
            <h3 class="text-lg font-bold text-gray-900 mb-6 pb-3 border-b-2 border-gray-200 flex items-center">
                <i class="fas fa-calendar-week text-blue-600 mr-2"></i>Horários da Semana
            </h3>
            
            <div class="space-y-4">
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
                    $schedules = $template->weeklySchedules->keyBy('day_of_week');
                @endphp

                @foreach($days as $dayNum => $dayName)
                    @php
                        $schedule = $schedules->get($dayNum);
                        $isWorkDay = $schedule && $schedule->is_work_day;
                    @endphp
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <div class="flex items-center justify-between mb-3">
                            <label class="font-semibold text-gray-700">{{ $dayName }}</label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="schedules[{{ $dayNum }}][is_work_day]" value="1" 
                                    {{ $isWorkDay ? 'checked' : '' }}
                                    onchange="toggleDayInputs({{ $dayNum }})" 
                                    id="is_work_day_{{ $dayNum }}" class="rounded">
                                <span class="text-sm">Dia de trabalho</span>
                            </label>
                        </div>
                        
                        <input type="hidden" name="schedules[{{ $dayNum }}][day_of_week]" value="{{ $dayNum }}">
                        
                        <div id="day_inputs_{{ $dayNum }}" class="grid grid-cols-2 md:grid-cols-4 gap-3" style="display: {{ $isWorkDay ? 'grid' : 'none' }}">
                            <div>
                                <label class="block text-sm mb-1">Entrada 1</label>
                                <input type="time" name="schedules[{{ $dayNum }}][entry_1]" 
                                    value="{{ $schedule ? substr($schedule->entry_1, 0, 5) : '' }}"
                                    class="w-full border rounded px-2 py-1 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Saída 1</label>
                                <input type="time" name="schedules[{{ $dayNum }}][exit_1]" 
                                    value="{{ $schedule ? substr($schedule->exit_1, 0, 5) : '' }}"
                                    class="w-full border rounded px-2 py-1 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Entrada 2</label>
                                <input type="time" name="schedules[{{ $dayNum }}][entry_2]" 
                                    value="{{ $schedule ? substr($schedule->entry_2, 0, 5) : '' }}"
                                    class="w-full border rounded px-2 py-1 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Saída 2</label>
                                <input type="time" name="schedules[{{ $dayNum }}][exit_2]" 
                                    value="{{ $schedule ? substr($schedule->exit_2, 0, 5) : '' }}"
                                    class="w-full border rounded px-2 py-1 text-sm">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($template->type === 'rotating_shift')
            {{-- Formulário para Escala Rotativa --}}
            @php
                $rule = $template->rotatingRule;
            @endphp
            
            <h3 class="text-lg font-bold text-gray-900 mb-6 pb-3 border-b-2 border-gray-200 flex items-center">
                <i class="fas fa-sync-alt text-blue-600 mr-2"></i>Configuração da Escala
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Dias de Trabalho no Ciclo *</label>
                    <input type="number" name="work_days" value="{{ old('work_days', $rule->work_days) }}" 
                        min="1" max="30" required
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Dias de Descanso no Ciclo *</label>
                    <input type="number" name="rest_days" value="{{ old('rest_days', $rule->rest_days) }}" 
                        min="1" max="30" required
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Horário de Início</label>
                    <input type="time" name="shift_start_time" 
                        value="{{ $rule->shift_start_time ? substr($rule->shift_start_time, 0, 5) : '' }}"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Horário de Término</label>
                    <input type="time" name="shift_end_time" 
                        value="{{ $rule->shift_end_time ? substr($rule->shift_end_time, 0, 5) : '' }}"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Duração (horas)</label>
                    <input type="number" name="shift_duration_hours" 
                        value="{{ old('shift_duration_hours', $rule->shift_duration_hours) }}" 
                        step="0.5" min="1" max="24"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

        @elseif($template->type === 'weekly_hours')
            {{-- Formulário para Carga Horária Flexível --}}
            @php
                $config = $template->flexibleHours;
            @endphp
            
            <h3 class="text-lg font-bold text-gray-900 mb-6 pb-3 border-b-2 border-gray-200 flex items-center">
                <i class="fas fa-clock text-blue-600 mr-2"></i>Configuração da Carga Horária
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Carga Horária Semanal *</label>
                    <input type="number" name="weekly_hours_required" 
                        value="{{ old('weekly_hours_required', $config->weekly_hours_required) }}" 
                        step="0.5" min="1" max="60" required
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Período de Apuração</label>
                    <select name="period_type" 
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                        <option value="weekly" {{ $config->period_type === 'weekly' ? 'selected' : '' }}>Semanal</option>
                        <option value="biweekly" {{ $config->period_type === 'biweekly' ? 'selected' : '' }}>Quinzenal</option>
                        <option value="monthly" {{ $config->period_type === 'monthly' ? 'selected' : '' }}>Mensal</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tolerância (minutos)</label>
                    <input type="number" name="grace_minutes" 
                        value="{{ old('grace_minutes', $config->grace_minutes) }}" 
                        min="0" max="60"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="flex items-center gap-2 mt-8">
                        <input type="checkbox" name="requires_minimum_daily_hours" value="1" 
                            {{ $config->requires_minimum_daily_hours ? 'checked' : '' }}
                            class="rounded">
                        <span class="text-sm font-medium">Exigir mínimo de horas por dia</span>
                    </label>
                </div>
            </div>

        @endif

        <div class="flex gap-3 mt-8">
            <a href="{{ route('work-shift-templates.index') }}" 
                class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" 
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i>Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
function toggleDayInputs(dayNum) {
    const checkbox = document.getElementById('is_work_day_' + dayNum);
    const inputs = document.getElementById('day_inputs_' + dayNum);
    
    if (checkbox.checked) {
        inputs.style.display = 'grid';
    } else {
        inputs.style.display = 'none';
        // Limpar inputs quando desmarcar
        inputs.querySelectorAll('input[type="time"]').forEach(input => {
            input.value = '';
        });
    }
}
</script>
@endsection
