@extends('layouts.app')
@section('title', 'Pré-visualização da Importação')
@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Pré-visualização da Importação</h1>
        <a href="{{ route('employee-imports.create') }}" class="text-gray-600 hover:text-gray-800">← Voltar</a>
    </div>

    <!-- Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-100 rounded-lg p-4">
            <div class="text-blue-600 text-sm font-medium">Total de Linhas</div>
            <div class="text-3xl font-bold text-blue-900">{{ $preview['total_rows'] }}</div>
        </div>
        <div class="bg-green-100 rounded-lg p-4">
            <div class="text-green-600 text-sm font-medium">Novos Colaboradores</div>
            <div class="text-3xl font-bold text-green-900">{{ $preview['new_employees'] }}</div>
        </div>
        <div class="bg-yellow-100 rounded-lg p-4">
            <div class="text-yellow-600 text-sm font-medium">Atualizações</div>
            <div class="text-3xl font-bold text-yellow-900">{{ $preview['existing_employees'] }}</div>
        </div>
        <div class="bg-red-100 rounded-lg p-4">
            <div class="text-red-600 text-sm font-medium">Erros</div>
            <div class="text-3xl font-bold text-red-900">{{ $preview['invalid_rows'] }}</div>
        </div>
    </div>

    <!-- Erros -->
    @if(!empty($preview['errors']))
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-red-900 mb-4">⚠️ Erros Encontrados</h3>
        <div class="space-y-3">
            @foreach($preview['errors'] as $error)
            <div class="bg-white p-3 rounded border border-red-300">
                <div class="font-semibold text-red-700">Linha {{ $error['line'] }}:</div>
                <ul class="list-disc list-inside text-red-600 text-sm mt-1">
                    @foreach($error['errors'] as $msg)
                    <li>{{ $msg }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
        <p class="text-sm text-red-700 mt-4">
            ⚠️ <strong>Atenção:</strong> Linhas com erros serão ignoradas durante a importação.
        </p>
    </div>
    @endif

    <!-- Amostra dos Dados -->
    @if(!empty($preview['sample_data']))
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Amostra dos Dados (Primeiras 5 Linhas Válidas)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">CPF</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PIS/PASEP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estabelecimento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departamento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admissão</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($preview['sample_data'] as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $row['cpf'] }}</td>
                        <td class="px-4 py-3 text-sm">{{ $row['full_name'] }}</td>
                        <td class="px-4 py-3 text-sm">{{ $row['pis_pasep'] }}</td>
                        <td class="px-4 py-3 text-sm">{{ $row['establishment_id'] }}</td>
                        <td class="px-4 py-3 text-sm">{{ $row['department_id'] }}</td>
                        <td class="px-4 py-3 text-sm">{{ $row['admission_date'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Ações -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div class="text-gray-600">
                @if($preview['valid_rows'] > 0)
                    <p>✅ Você pode processar <strong>{{ $preview['valid_rows'] }}</strong> registro(s) válido(s).</p>
                @else
                    <p>❌ Não há registros válidos para processar.</p>
                @endif
            </div>
            <div class="space-x-3">
                <a href="{{ route('employee-imports.create') }}" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 inline-block">
                    Cancelar
                </a>
                @if($preview['valid_rows'] > 0)
                <form method="POST" action="{{ route('employee-imports.process', $import) }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-medium">
                        ✅ Confirmar e Processar Importação
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
