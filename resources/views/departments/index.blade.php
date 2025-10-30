@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Departamentos</h1>
    <a href="{{ route('departments.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ Novo Departamento</a>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Nome</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Estabelecimento</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Responsável</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Funcionários</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departments as $dept)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4">{{ $dept->name }}</td>
                <td class="px-6 py-4">{{ $dept->establishment->corporate_name }}</td>
                <td class="px-6 py-4">{{ $dept->responsible ?? '-' }}</td>
                <td class="px-6 py-4">{{ $dept->employees->count() }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('departments.show', $dept) }}" class="text-blue-500 hover:underline mr-3">Ver</a>
                    <a href="{{ route('departments.edit', $dept) }}" class="text-yellow-500 hover:underline mr-3">Editar</a>
                    <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline" onclick="return confirm('Tem certeza?')">Excluir</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum departamento cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
