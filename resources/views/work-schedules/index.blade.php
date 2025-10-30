@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Escalas de Trabalho - {{ $employee->full_name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('employees.work-schedules.create', $employee) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ Nova Escala</a>
        <a href="{{ route('employees.show', $employee) }}" class="bg-gray-300 px-4 py-2 rounded">Voltar</a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
@endif

<!-- SELETOR DE TEMPLATE DE JORNADA -->
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-start gap-4">
        <div class="bg-blue-500 text-white rounded-full p-3 mt-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-800 mb-2">⚡ Aplicar Jornada Pré-Configurada</h2>
            <p class="text-gray-600 mb-4">Selecione um modelo de jornada e aplique instantaneamente ao colaborador. Isso substituirá todos os horários atuais.</p>
            
            @if($currentAssignment && $currentAssignment->template)
                <div class="bg-green-50 border border-green-200 rounded px-3 py-2 mb-4">
                    <span class="text-green-800 font-medium">✅ Jornada Atual: {{ $currentAssignment->template->name }}</span>
                    <span class="text-green-600 text-sm ml-2">({{ $currentAssignment->template->weekly_hours }}h/semana)</span>
                </div>
            @endif

            <form action="{{ route('employees.work-schedules.apply-template', $employee) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Selecione a Jornada:</label>
                        <select name="template_id" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Escolha uma jornada --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ $currentAssignment && $currentAssignment->template_id == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ $template->weekly_hours }}h/semana)
                                    @if($template->is_preset) ⭐ @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Válido a partir de:</label>
                        <input type="date" name="effective_from" value="{{ date('Y-m-d') }}" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold px-6 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 shadow-md hover:shadow-lg transition-all">
                        🚀 Aplicar Jornada
                    </button>
                    <button type="button" onclick="document.getElementById('template-preview').classList.toggle('hidden')" class="bg-gray-200 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-300">
                        👁️ Ver Detalhes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview dos templates (inicialmente oculto) -->
<div id="template-preview" class="hidden bg-white border rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-bold mb-4">📋 Detalhes das Jornadas Disponíveis</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($templates->where('type', 'weekly') as $template)
            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                <h4 class="font-bold text-blue-600 mb-2">{{ $template->name }}</h4>
                <p class="text-sm text-gray-600 mb-3">Carga horária: {{ $template->weekly_hours }}h/semana</p>
                @if($template->weeklySchedules->count() > 0)
                    <div class="text-xs space-y-1">
                        @foreach($template->weeklySchedules->where('is_work_day', true) as $schedule)
                            <div class="flex justify-between">
                                <span class="font-medium">{{ $schedule->day_name }}:</span>
                                <span>{{ $schedule->entry_1 ? substr($schedule->entry_1, 0, 5) : '' }} - {{ $schedule->exit_1 ? substr($schedule->exit_1, 0, 5) : '' }}
                                @if($schedule->entry_2)
                                    | {{ substr($schedule->entry_2, 0, 5) }} - {{ substr($schedule->exit_2, 0, 5) }}
                                @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Dia da Semana</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Entrada 1</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Saída 1</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Entrada 2</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Saída 2</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Entrada 3</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Saída 3</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $schedule)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4">
                    @switch($schedule->day_of_week)
                        @case(0) Domingo @break
                        @case(1) Segunda-feira @break
                        @case(2) Terça-feira @break
                        @case(3) Quarta-feira @break
                        @case(4) Quinta-feira @break
                        @case(5) Sexta-feira @break
                        @case(6) Sábado @break
                    @endswitch
                </td>
                <td class="px-6 py-4">{{ $schedule->entry_1 ? substr($schedule->entry_1, 0, 5) : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->exit_1 ? substr($schedule->exit_1, 0, 5) : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->entry_2 ? substr($schedule->entry_2, 0, 5) : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->exit_2 ? substr($schedule->exit_2, 0, 5) : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->entry_3 ? substr($schedule->entry_3, 0, 5) : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->exit_3 ? substr($schedule->exit_3, 0, 5) : '-' }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('employees.work-schedules.edit', [$employee, $schedule]) }}" class="text-yellow-500 hover:underline mr-3">Editar</a>
                    <form action="{{ route('employees.work-schedules.destroy', [$employee, $schedule]) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline" onclick="return confirm('Tem certeza?')">Excluir</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">Nenhuma escala cadastrada. Adicione os horários de trabalho para cada dia da semana.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
