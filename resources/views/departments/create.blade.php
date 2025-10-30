@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold mb-6">Novo Departamento</h1>
<div class="bg-white rounded shadow p-6 max-w-2xl">
    <form action="{{ route('departments.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block font-medium mb-1">Estabelecimento *</label>
            <select name="establishment_id" required class="w-full border rounded px-3 py-2">
                <option value="">Selecione...</option>
                @foreach($establishments as $est)
                    <option value="{{ $est->id }}">{{ $est->corporate_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block font-medium mb-1">Nome *</label>
            <input type="text" name="name" required class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block font-medium mb-1">Responsável</label>
            <input type="text" name="responsible" class="w-full border rounded px-3 py-2">
        </div>
        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Salvar</button>
            <a href="{{ route('departments.index') }}" class="bg-gray-300 px-6 py-2 rounded hover:bg-gray-400">Cancelar</a>
        </div>
    </form>
</div>
@endsection
