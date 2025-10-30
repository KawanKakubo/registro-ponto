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
                <td class="px-6 py-4">{{ $schedule->entry_1 ? \Carbon\Carbon::parse($schedule->entry_1)->format('H:i') : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->exit_1 ? \Carbon\Carbon::parse($schedule->exit_1)->format('H:i') : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->entry_2 ? \Carbon\Carbon::parse($schedule->entry_2)->format('H:i') : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->exit_2 ? \Carbon\Carbon::parse($schedule->exit_2)->format('H:i') : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->entry_3 ? \Carbon\Carbon::parse($schedule->entry_3)->format('H:i') : '-' }}</td>
                <td class="px-6 py-4">{{ $schedule->exit_3 ? \Carbon\Carbon::parse($schedule->exit_3)->format('H:i') : '-' }}</td>
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
