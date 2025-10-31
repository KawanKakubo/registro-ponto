@extends('layouts.app')
@section('title', 'Importar Arquivo AFD')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Importar Arquivo AFD</h1>
    <div class="bg-white rounded shadow p-6">
        <form action="{{ route('afd-imports.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Arquivo AFD (.txt)</label>
                <input type="file" name="afd_file" accept=".txt" required
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('afd_file')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
                <p class="text-sm text-blue-800">
                    <strong>Importante:</strong> O arquivo AFD deve estar no formato especificado pela Portaria 671/2021.
                    Todos os colaboradores devem estar previamente cadastrados no sistema.
                </p>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Importar
                </button>
                <a href="{{ route('afd-imports.index') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
