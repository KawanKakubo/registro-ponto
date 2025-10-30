@extends('layouts.app')
@section('title', 'Gerar Cartão de Ponto')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Gerar Cartão de Ponto</h1>
    <div class="bg-white rounded shadow p-6">
        <form action="{{ route('timesheets.generate') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Colaborador</label>
                <select name="employee_id" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione um colaborador</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }} - {{ $employee->cpf }}</option>
                    @endforeach
                </select>
                @error('employee_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Data Inicial</label>
                    <input type="date" name="start_date" required
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Data Final</label>
                    <input type="date" name="end_date" required
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600 font-medium">
                Gerar Cartão de Ponto
            </button>
        </form>
    </div>
</div>
@endsection
