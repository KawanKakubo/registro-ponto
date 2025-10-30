@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold mb-6">Novo Colaborador</h1>
<div class="bg-white rounded shadow p-6 max-w-2xl">
    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="col-span-2">
                <label class="block font-medium mb-1">Nome Completo *</label>
                <input type="text" name="full_name" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">CPF *</label>
                <input type="text" name="cpf" required class="w-full border rounded px-3 py-2" placeholder="000.000.000-00">
            </div>
            <div>
                <label class="block font-medium mb-1">PIS/PASEP</label>
                <input type="text" name="pis_pasep" class="w-full border rounded px-3 py-2" placeholder="000.00000.00-0">
            </div>
            <div>
                <label class="block font-medium mb-1">Matrícula</label>
                <input type="text" name="matricula" class="w-full border rounded px-3 py-2" placeholder="Ex: 1234">
            </div>
            <div>
                <label class="block font-medium mb-1">CTPS</label>
                <input type="text" name="ctps" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Data de Admissão *</label>
                <input type="date" name="admission_date" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Estabelecimento *</label>
                <select name="establishment_id" required class="w-full border rounded px-3 py-2">
                    @foreach($establishments as $est)
                        <option value="{{ $est->id }}">{{ $est->corporate_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-medium mb-1">Departamento</label>
                <select name="department_id" class="w-full border rounded px-3 py-2">
                    <option value="">Nenhum</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-medium mb-1">Função</label>
                <input type="text" name="position" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Status *</label>
                <select name="status" required class="w-full border rounded px-3 py-2">
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                    <option value="on_leave">Afastado</option>
                </select>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Salvar</button>
            <a href="{{ route('employees.index') }}" class="bg-gray-300 px-6 py-2 rounded hover:bg-gray-400">Cancelar</a>
        </div>
    </form>
</div>
@endsection
