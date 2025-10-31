@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('employees.show', $employee) }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>Escalas de Trabalho
                </h1>
                <p class="text-gray-600 mt-2">
                    <i class="fas fa-user mr-1"></i>{{ $employee->full_name }}
                </p>
            </div>
        </div>
        <a href="{{ route('employees.work-schedules.create', $employee) }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg">
            <i class="fas fa-plus mr-2"></i>Nova Escala
        </a>
    </div>

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

    <!-- Template Selector -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-blue-600 rounded-full text-white">
                <i class="fas fa-clock text-2xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-bolt text-yellow-500 mr-1"></i>Aplicar Jornada Pré-Configurada
                </h2>
                <p class="text-gray-600 mb-4">Selecione um modelo de jornada e aplique instantaneamente. Isso substituirá todos os horários atuais.</p>
                
                @if($currentAssignment && $currentAssignment->template)
                <div class="bg-green-50 border-l-4 border-green-600 rounded-lg px-4 py-3 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 text-xl mr-2"></i>
                        <div>
                            <span class="text-green-800 font-bold">Jornada Atual: {{ $currentAssignment->template->name }}</span>
                            <span class="text-green-600 text-sm ml-2">({{ $currentAssignment->template->weekly_hours }}h/semana)</span>
                        </div>
                    </div>
                </div>
                @endif

                <form action="{{ route('employees.work-schedules.apply-template', $employee) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-tasks mr-1"></i>Selecione a Jornada:
                            </label>
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
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-calendar-check mr-1"></i>Válido a partir de:
                            </label>
                            <input type="date" name="effective_from" value="{{ date('Y-m-d') }}" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition">
                            <i class="fas fa-rocket mr-2"></i>Aplicar Jornada
                        </button>
                        <button type="button" onclick="document.getElementById('template-preview').classList.toggle('hidden')" class="inline-flex items-center px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                            <i class="fas fa-eye mr-2"></i>Ver Detalhes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Template Preview -->
    <div id="template-preview" class="hidden bg-white rounded-lg shadow-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-list text-blue-600 mr-2"></i>Detalhes das Jornadas Disponíveis
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($templates->where('type', 'weekly') as $template)
                <div class="border-2 border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-blue-300 transition">
                    <h4 class="font-bold text-blue-600 mb-2 flex items-center">
                        <i class="fas fa-briefcase mr-2"></i>{{ $template->name }}
                    </h4>
                    <p class="text-sm text-gray-600 mb-3">
                        <i class="fas fa-clock mr-1"></i>Carga horária: <strong>{{ $template->weekly_hours }}h/semana</strong>
                    </p>
                    @if($template->weeklySchedules->count() > 0)
                        <div class="text-xs space-y-1 bg-gray-50 p-3 rounded">
                            @foreach($template->weeklySchedules->where('is_work_day', true) as $schedule)
                                <div class="flex justify-between items-center py-1">
                                    <span class="font-semibold text-gray-700">{{ $schedule->day_name }}:</span>
                                    <span class="font-mono text-gray-900">
                                        {{ $schedule->entry_1 ? substr($schedule->entry_1, 0, 5) : '' }} - {{ $schedule->exit_1 ? substr($schedule->exit_1, 0, 5) : '' }}
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

    <!-- Schedules Table -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-table text-blue-600 mr-2"></i>Horários Configurados
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Dia da Semana</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Entrada 1</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Saída 1</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Entrada 2</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Saída 2</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Entrada 3</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Saída 3</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            @switch($schedule->day_of_week)
                                @case(0) <i class="fas fa-calendar-day text-red-500 mr-2"></i>Domingo @break
                                @case(1) <i class="fas fa-calendar-day text-blue-500 mr-2"></i>Segunda-feira @break
                                @case(2) <i class="fas fa-calendar-day text-green-500 mr-2"></i>Terça-feira @break
                                @case(3) <i class="fas fa-calendar-day text-yellow-500 mr-2"></i>Quarta-feira @break
                                @case(4) <i class="fas fa-calendar-day text-purple-500 mr-2"></i>Quinta-feira @break
                                @case(5) <i class="fas fa-calendar-day text-indigo-500 mr-2"></i>Sexta-feira @break
                                @case(6) <i class="fas fa-calendar-day text-orange-500 mr-2"></i>Sábado @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $schedule->entry_1 ? substr($schedule->entry_1, 0, 5) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $schedule->exit_1 ? substr($schedule->exit_1, 0, 5) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $schedule->entry_2 ? substr($schedule->entry_2, 0, 5) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $schedule->exit_2 ? substr($schedule->exit_2, 0, 5) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $schedule->entry_3 ? substr($schedule->entry_3, 0, 5) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $schedule->exit_3 ? substr($schedule->exit_3, 0, 5) : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('employees.work-schedules.edit', [$employee, $schedule]) }}" class="inline-flex items-center px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded transition">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>
                                <form action="{{ route('employees.work-schedules.destroy', [$employee, $schedule]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded transition" onclick="return confirm('Tem certeza que deseja excluir esta escala?')">
                                        <i class="fas fa-trash mr-1"></i>Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 mb-4">Nenhuma escala cadastrada</p>
                            <p class="text-sm text-gray-400 mb-4">Adicione os horários de trabalho para cada dia da semana</p>
                            <a href="{{ route('employees.work-schedules.create', $employee) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                                <i class="fas fa-plus mr-2"></i>Criar Primeira Escala
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
