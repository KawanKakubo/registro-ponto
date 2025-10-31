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

    <!-- Work Schedules -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Escalas de Trabalho
            </h3>
            <a href="{{ route('employees.work-schedules.create', $employee) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition text-sm">
                <i class="fas fa-plus mr-2"></i>Nova Escala
            </a>
        </div>
        <div class="p-6">
            @if($employee->workSchedules->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Dia da Semana</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Entrada 1</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Saída 1</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Entrada 2</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Saída 2</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Entrada 3</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Saída 3</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($employee->workSchedules->sortBy('day_of_week') as $schedule)
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
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->entry_1 ? \Carbon\Carbon::parse($schedule->entry_1)->format('H:i') : '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->exit_1 ? \Carbon\Carbon::parse($schedule->exit_1)->format('H:i') : '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->entry_2 ? \Carbon\Carbon::parse($schedule->entry_2)->format('H:i') : '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->exit_2 ? \Carbon\Carbon::parse($schedule->exit_2)->format('H:i') : '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->entry_3 ? \Carbon\Carbon::parse($schedule->entry_3)->format('H:i') : '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $schedule->exit_3 ? \Carbon\Carbon::parse($schedule->exit_3)->format('H:i') : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 mb-4">Nenhuma escala de trabalho cadastrada</p>
                    <a href="{{ route('employees.work-schedules.create', $employee) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>Criar Primeira Escala
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
