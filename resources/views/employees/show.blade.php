@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">{{ $employee->full_name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('employees.edit', $employee) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">Editar</a>
        <a href="{{ route('employees.index') }}" class="bg-gray-300 px-4 py-2 rounded">Voltar</a>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Informações do Funcionário</h2>
    <div class="grid grid-cols-2 gap-4">
        <div><strong>Nome:</strong> {{ $employee->full_name }}</div>
        <div><strong>CPF:</strong> {{ $employee->cpf }}</div>
        <div><strong>PIS/PASEP:</strong> {{ $employee->pis_pasep ?? '-' }}</div>
        <div><strong>CTPS:</strong> {{ $employee->ctps ?? '-' }}</div>
        <div><strong>Cargo:</strong> {{ $employee->position ?? '-' }}</div>
        <div><strong>Status:</strong> 
            <span class="px-2 py-1 rounded text-sm
                @if($employee->status === 'active') bg-green-100 text-green-800
                @elseif($employee->status === 'inactive') bg-red-100 text-red-800
                @else bg-yellow-100 text-yellow-800 @endif">
                {{ ucfirst($employee->status) }}
            </span>
        </div>
        <div><strong>Estabelecimento:</strong> {{ $employee->establishment->corporate_name }}</div>
        <div><strong>Departamento:</strong> {{ $employee->department->name ?? '-' }}</div>
        <div><strong>Data de Admissão:</strong> {{ $employee->admission_date?->format('d/m/Y') ?? '-' }}</div>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Escalas de Trabalho</h2>
        <a href="{{ route('employees.work-schedules.create', $employee) }}" class="bg-green-500 text-white px-4 py-2 rounded text-sm">+ Nova Escala</a>
    </div>
    @if($employee->workSchedules->count() > 0)
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Dia da Semana</th>
                    <th class="text-left py-2">Entrada 1</th>
                    <th class="text-left py-2">Saída 1</th>
                    <th class="text-left py-2">Entrada 2</th>
                    <th class="text-left py-2">Saída 2</th>
                    <th class="text-left py-2">Entrada 3</th>
                    <th class="text-left py-2">Saída 3</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->workSchedules->sortBy('day_of_week') as $schedule)
                <tr class="border-b">
                    <td class="py-2">
                        @switch($schedule->day_of_week)
                            @case(0) Domingo @break
                            @case(1) Segunda @break
                            @case(2) Terça @break
                            @case(3) Quarta @break
                            @case(4) Quinta @break
                            @case(5) Sexta @break
                            @case(6) Sábado @break
                        @endswitch
                    </td>
                    <td class="py-2">{{ $schedule->entry_1 ? \Carbon\Carbon::parse($schedule->entry_1)->format('H:i') : '-' }}</td>
                    <td class="py-2">{{ $schedule->exit_1 ? \Carbon\Carbon::parse($schedule->exit_1)->format('H:i') : '-' }}</td>
                    <td class="py-2">{{ $schedule->entry_2 ? \Carbon\Carbon::parse($schedule->entry_2)->format('H:i') : '-' }}</td>
                    <td class="py-2">{{ $schedule->exit_2 ? \Carbon\Carbon::parse($schedule->exit_2)->format('H:i') : '-' }}</td>
                    <td class="py-2">{{ $schedule->entry_3 ? \Carbon\Carbon::parse($schedule->entry_3)->format('H:i') : '-' }}</td>
                    <td class="py-2">{{ $schedule->exit_3 ? \Carbon\Carbon::parse($schedule->exit_3)->format('H:i') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500">Nenhuma escala de trabalho cadastrada.</p>
    @endif
</div>

<div class="bg-white rounded shadow p-6">
    <h2 class="text-lg font-semibold mb-4">Registros de Ponto Recentes</h2>
    @if($employee->timeRecords->count() > 0)
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Data/Hora</th>
                    <th class="text-left py-2">NSR</th>
                    <th class="text-left py-2">Importação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->timeRecords->take(10) as $record)
                <tr class="border-b">
                    <td class="py-2">{{ $record->recorded_at->format('d/m/Y H:i:s') }}</td>
                    <td class="py-2">{{ $record->nsr ?? '-' }}</td>
                    <td class="py-2">{{ $record->afd_file_name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500">Nenhum registro de ponto encontrado.</p>
    @endif
</div>
@endsection
