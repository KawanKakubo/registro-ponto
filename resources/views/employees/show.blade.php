@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                    </div>
                    {{ $employee->full_name }}
                    @if($employee->status === 'active')
                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Ativo
                        </span>
                    @elseif($employee->status === 'inactive')
                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>Inativo
                        </span>
                    @else
                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>{{ ucfirst($employee->status) }}
                        </span>
                    @endif
                </h1>
                <p class="text-gray-600 mt-2">
                    <i class="fas fa-id-card mr-1"></i>CPF: {{ $employee->cpf }}
                    @if($employee->matricula)
                        | <i class="fas fa-hashtag mr-1"></i>Matrícula: {{ $employee->matricula }}
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition shadow-lg">
            <i class="fas fa-edit mr-2"></i>Editar Colaborador
        </a>
    </div>

    <!-- Employee Information -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-user text-blue-600 mr-2"></i>Informações do Colaborador
            </h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <div class="flex items-start">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Nome Completo</p>
                    <p class="font-semibold text-gray-900">{{ $employee->full_name }}</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-id-card text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">CPF</p>
                    <p class="font-semibold text-gray-900 font-mono">{{ $employee->cpf }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-purple-100 rounded-lg mr-3">
                    <i class="fas fa-fingerprint text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">PIS/PASEP</p>
                    <p class="font-semibold text-gray-900 font-mono">{{ $employee->pis_pasep ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-hashtag text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Matrícula</p>
                    <p class="font-semibold text-gray-900">{{ $employee->matricula ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                    <i class="fas fa-id-badge text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">CTPS</p>
                    <p class="font-semibold text-gray-900">{{ $employee->ctps ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-indigo-100 rounded-lg mr-3">
                    <i class="fas fa-briefcase text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Cargo</p>
                    <p class="font-semibold text-gray-900">{{ $employee->position ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Estabelecimento</p>
                    <p class="font-semibold text-gray-900">{{ $employee->establishment->corporate_name }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-purple-100 rounded-lg mr-3">
                    <i class="fas fa-sitemap text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Departamento</p>
                    <p class="font-semibold text-gray-900">{{ $employee->department->name ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-calendar-plus text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Data de Admissão</p>
                    <p class="font-semibold text-gray-900">{{ $employee->admission_date?->format('d/m/Y') ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-gray-100 rounded-lg mr-3">
                    <i class="fas fa-flag text-gray-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    @if($employee->status === 'active')
                        <span class="inline-flex items-center px-2 py-1 rounded text-sm font-semibold bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Ativo
                        </span>
                    @elseif($employee->status === 'inactive')
                        <span class="inline-flex items-center px-2 py-1 rounded text-sm font-semibold bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>Inativo
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded text-sm font-semibold bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>{{ ucfirst($employee->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Work Shift Assignment -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-business-time text-blue-600 mr-2"></i>Jornada de Trabalho
            </h3>
        </div>
        <div class="p-6">
            @php
                $activeAssignment = $employee->workShiftAssignments()
                    ->with(['template.weeklySchedules', 'template.rotatingRule', 'template.flexibleHours'])
                    ->where(function($q) {
                        $q->whereNull('effective_until')
                          ->orWhere('effective_until', '>=', now());
                    })
                    ->orderBy('effective_from', 'desc')
                    ->first();
            @endphp
            
            @if($activeAssignment)
                <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-bold text-blue-900 mb-1">
                                {{ $activeAssignment->template->name }}
                            </h4>
                            <p class="text-sm text-blue-700">
                                @if($activeAssignment->template->type === 'weekly')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                                        <i class="fas fa-calendar-week mr-1"></i> Semanal Fixa
                                    </span>
                                @elseif($activeAssignment->template->type === 'rotating_shift')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800">
                                        <i class="fas fa-sync-alt mr-1"></i> Escala Rotativa
                                    </span>
                                @elseif($activeAssignment->template->type === 'weekly_hours')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">
                                        <i class="fas fa-clock mr-1"></i> Carga Horária Flexível
                                    </span>
                                @endif
                                <span class="ml-2">• Vigente desde {{ $activeAssignment->effective_from->format('d/m/Y') }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                @if($activeAssignment->template->type === 'weekly')
                    {{-- Jornada Semanal Fixa - mostra horários por dia da semana --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Dia da Semana</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Entrada 1</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Saída 1</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Entrada 2</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Saída 2</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($activeAssignment->template->weeklySchedules->sortBy('day_of_week') as $schedule)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        @switch($schedule->day_of_week)
                                            @case(0) <i class="fas fa-calendar-day text-red-500 mr-2"></i>Domingo @break
                                            @case(1) <i class="fas fa-calendar-day text-blue-500 mr-2"></i>Segunda @break
                                            @case(2) <i class="fas fa-calendar-day text-green-500 mr-2"></i>Terça @break
                                            @case(3) <i class="fas fa-calendar-day text-yellow-500 mr-2"></i>Quarta @break
                                            @case(4) <i class="fas fa-calendar-day text-purple-500 mr-2"></i>Quinta @break
                                            @case(5) <i class="fas fa-calendar-day text-indigo-500 mr-2"></i>Sexta @break
                                            @case(6) <i class="fas fa-calendar-day text-orange-500 mr-2"></i>Sábado @break
                                        @endswitch
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->entry_1 ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->exit_1 ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->entry_2 ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->exit_2 ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($schedule->daily_hours, 2) }}h</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif($activeAssignment->template->type === 'rotating_shift')
                    {{-- Escala Rotativa --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Ciclo de Trabalho</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $activeAssignment->template->rotatingRule->work_days }}x{{ $activeAssignment->template->rotatingRule->rest_days }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $activeAssignment->template->rotatingRule->work_days }} dia(s) de trabalho, 
                                {{ $activeAssignment->template->rotatingRule->rest_days }} dia(s) de descanso
                            </p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Horário do Turno</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $activeAssignment->template->rotatingRule->shift_start_time }} - {{ $activeAssignment->template->rotatingRule->shift_end_time }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ number_format($activeAssignment->template->rotatingRule->shift_duration_hours, 2) }} horas por turno
                            </p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg col-span-2">
                            <p class="text-sm text-gray-600 mb-1">Início do Ciclo</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ $activeAssignment->cycle_start_date ? $activeAssignment->cycle_start_date->format('d/m/Y') : 'Não definido' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Os dias de trabalho são calculados automaticamente a partir desta data
                            </p>
                        </div>
                    </div>
                @elseif($activeAssignment->template->type === 'weekly_hours')
                    {{-- Carga Horária Flexível --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Carga Horária</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $activeAssignment->template->flexibleHours->weekly_hours_required }}h
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Por {{ $activeAssignment->template->flexibleHours->period_type === 'week' ? 'semana' : 'mês' }}
                            </p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Tolerância</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $activeAssignment->template->flexibleHours->grace_minutes ?? 0 }} min
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Margem de flexibilidade
                            </p>
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 mb-4">Nenhuma jornada de trabalho atribuída</p>
                    <a href="{{ route('work-shift-templates.bulk-assign') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                        <i class="fas fa-user-clock mr-2"></i>Atribuir Jornada
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Time Records -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-clock text-blue-600 mr-2"></i>Registros de Ponto Recentes
                <span class="text-sm font-normal text-gray-600 ml-2">(Últimos 10 registros)</span>
            </h3>
        </div>
        <div class="p-6">
            @if($employee->timeRecords->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                                    <i class="fas fa-calendar-alt mr-1"></i>Data/Hora
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                                    <i class="fas fa-hashtag mr-1"></i>NSR
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                                    <i class="fas fa-file-import mr-1"></i>Importação
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($employee->timeRecords->take(10) as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-clock text-blue-500 mr-2"></i>{{ $record->recorded_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $record->nsr ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $record->afd_file_name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-clock text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Nenhum registro de ponto encontrado</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
