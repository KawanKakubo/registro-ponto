@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employee-imports.create') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-eye text-blue-600 mr-3"></i>Pré-visualização da Importação
            </h1>
            <p class="text-gray-600 mt-2">Revise os dados antes de confirmar a importação</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-white/20 mr-4">
                    <i class="fas fa-list text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium opacity-90">Total de Linhas</p>
                    <p class="text-3xl font-bold">{{ $preview['total_rows'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-white/20 mr-4">
                    <i class="fas fa-user-plus text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium opacity-90">Novos Colaboradores</p>
                    <p class="text-3xl font-bold">{{ $preview['new_employees'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-white/20 mr-4">
                    <i class="fas fa-sync text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium opacity-90">Atualizações</p>
                    <p class="text-3xl font-bold">{{ $preview['existing_employees'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-white/20 mr-4">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium opacity-90">Erros</p>
                    <p class="text-3xl font-bold">{{ $preview['invalid_rows'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Errors Section -->
    @if(!empty($preview['errors']))
    <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-6 mb-6">
        <div class="flex items-start mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-4 mt-1"></i>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-red-900 mb-2">Erros Encontrados</h3>
                <p class="text-sm text-red-700 mb-4">
                    <i class="fas fa-info-circle mr-1"></i>As linhas com erros serão ignoradas durante a importação
                </p>
            </div>
        </div>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($preview['errors'] as $error)
            <div class="bg-white rounded-lg p-4 border border-red-200 shadow-sm">
                <div class="flex items-start">
                    <div class="bg-red-100 text-red-800 font-bold px-3 py-1 rounded text-sm mr-3">
                        Linha {{ $error['line'] }}
                    </div>
                    <ul class="flex-1 space-y-1">
                        @foreach($error['errors'] as $msg)
                        <li class="text-sm text-red-700 flex items-start">
                            <i class="fas fa-times-circle mr-2 mt-0.5"></i>
                            <span>{{ $msg }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Sample Data -->
    @if(!empty($preview['sample_data']))
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-table text-blue-600 mr-2"></i>Amostra dos Dados
                <span class="text-sm font-normal text-gray-600 ml-2">(Primeiras 5 linhas válidas)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-id-card mr-1"></i>CPF
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-user mr-1"></i>Nome
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-fingerprint mr-1"></i>PIS/PASEP
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-building mr-1"></i>Estabelecimento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-sitemap mr-1"></i>Departamento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-calendar mr-1"></i>Admissão
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($preview['sample_data'] as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['cpf'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $row['full_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $row['pis_pasep'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $row['establishment_id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $row['department_id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $row['admission_date'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @if($preview['valid_rows'] > 0)
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    <div>
                        <p class="font-semibold text-gray-900">Pronto para processar</p>
                        <p class="text-sm text-gray-600">{{ $preview['valid_rows'] }} registro(s) válido(s)</p>
                    </div>
                @else
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                    <div>
                        <p class="font-semibold text-gray-900">Não há registros válidos</p>
                        <p class="text-sm text-gray-600">Corrija os erros e tente novamente</p>
                    </div>
                @endif
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('employee-imports.create') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                @if($preview['valid_rows'] > 0)
                <form method="POST" action="{{ route('employee-imports.process', $import) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                        <i class="fas fa-check mr-2"></i>Confirmar e Processar
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
