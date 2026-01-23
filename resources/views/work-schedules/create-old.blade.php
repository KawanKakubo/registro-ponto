@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold mb-6">Nova Escala de Trabalho - {{ $employee->full_name }}</h1>
<div class="bg-white rounded shadow p-6 max-w-3xl">
    <form action="{{ route('employees.work-schedules.store', $employee) }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block font-medium mb-1">Dia da Semana *</label>
            <select name="day_of_week" required class="w-full border rounded px-3 py-2">
                <option value="">Selecione...</option>
                <option value="0">Domingo</option>
                <option value="1">Segunda-feira</option>
                <option value="2">Terça-feira</option>
                <option value="3">Quarta-feira</option>
                <option value="4">Quinta-feira</option>
                <option value="5">Sexta-feira</option>
                <option value="6">Sábado</option>
            </select>
        </div>
        
        <div class="border-t pt-4 mb-4">
            <h3 class="font-semibold mb-3">Período 1</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">Entrada 1</label>
                    <input type="time" name="entry_1" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">Saída 1</label>
                    <input type="time" name="exit_1" class="w-full border rounded px-3 py-2">
                </div>
            </div>
        </div>
        
        <div class="border-t pt-4 mb-4">
            <h3 class="font-semibold mb-3">Período 2 (opcional)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">Entrada 2</label>
                    <input type="time" name="entry_2" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">Saída 2</label>
                    <input type="time" name="exit_2" class="w-full border rounded px-3 py-2">
                </div>
            </div>
        </div>
        
        <div class="border-t pt-4 mb-4">
            <h3 class="font-semibold mb-3">Período 3 (opcional)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">Entrada 3</label>
                    <input type="time" name="entry_3" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">Saída 3</label>
                    <input type="time" name="exit_3" class="w-full border rounded px-3 py-2">
                </div>
            </div>
        </div>
        
        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Salvar</button>
            <a href="{{ route('employees.work-schedules.index', $employee) }}" class="bg-gray-300 px-6 py-2 rounded hover:bg-gray-400">Cancelar</a>
        </div>
    </form>
</div>
@endsection
