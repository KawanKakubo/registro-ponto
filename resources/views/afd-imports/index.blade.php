@extends('layouts.app')
@section('content')
<div class="flex justify-between mb-6">
    <h1 class="text-2xl font-bold">Importações AFD</h1>
    <a href="{{ route('afd-imports.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Nova Importação</a>
</div>
<div class="bg-white rounded shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Arquivo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registros</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($imports as $import)
            <tr>
                <td class="px-6 py-4">{{ $import->file_name }}</td>
                <td class="px-6 py-4">{{ $import->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-6 py-4">{{ $import->total_records }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded @if($import->status == 'completed') bg-green-100 text-green-800 @elseif($import->status == 'failed') bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif">
                        {{ ucfirst($import->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('afd-imports.show', $import) }}" class="text-blue-600 hover:underline">Ver Detalhes</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhuma importação realizada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
