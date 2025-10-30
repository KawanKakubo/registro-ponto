@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Estabelecimentos</h1>
    <a href="{{ route('establishments.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ Novo Estabelecimento</a>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Razão Social</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Nome Fantasia</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">CNPJ</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Cidade</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">UF</th>
                <th class="text-left px-6 py-3 font-medium text-gray-700">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($establishments as $est)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4">{{ $est->corporate_name }}</td>
                <td class="px-6 py-4">{{ $est->trade_name }}</td>
                <td class="px-6 py-4">{{ $est->cnpj }}</td>
                <td class="px-6 py-4">{{ $est->city }}</td>
                <td class="px-6 py-4">{{ $est->state }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('establishments.show', $est) }}" class="text-blue-500 hover:underline mr-3">Ver</a>
                    <a href="{{ route('establishments.edit', $est) }}" class="text-yellow-500 hover:underline mr-3">Editar</a>
                    <form action="{{ route('establishments.destroy', $est) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline" onclick="return confirm('Tem certeza?')">Excluir</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum estabelecimento cadastrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
