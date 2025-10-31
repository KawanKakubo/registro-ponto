@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Saudação -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Bem-vindo(a) de volta, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-600 mt-2">Aqui está um resumo das atividades do sistema</p>
    </div>

    <!-- Cartões de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Colaboradores Ativos -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Colaboradores Ativos</p>
                    <p class="text-4xl font-bold">{{ \App\Models\Employee::count() }}</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-users text-3xl"></i>
                </div>
            </div>
            <a href="{{ route('employees.index') }}" class="text-blue-100 hover:text-white text-sm flex items-center">
                Ver todos <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <!-- Estabelecimentos -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Estabelecimentos</p>
                    <p class="text-4xl font-bold">{{ \App\Models\Establishment::count() }}</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-building text-3xl"></i>
                </div>
            </div>
            <a href="{{ route('establishments.index') }}" class="text-green-100 hover:text-white text-sm flex items-center">
                Ver todos <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <!-- Marcações Hoje -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Marcações Hoje</p>
                    <p class="text-4xl font-bold">{{ \App\Models\TimeRecord::whereDate('record_date', today())->count() }}</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-clock text-3xl"></i>
                </div>
            </div>
            <span class="text-purple-100 text-sm">{{ today()->format('d/m/Y') }}</span>
        </div>

        <!-- Importações Pendentes -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Importações Pendentes</p>
                    <p class="text-4xl font-bold">{{ \App\Models\AfdImport::where('status', 'pending')->count() }}</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-spinner text-3xl"></i>
                </div>
            </div>
            <a href="{{ route('afd-imports.index') }}" class="text-orange-100 hover:text-white text-sm flex items-center">
                Ver fila <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Ações Rápidas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('afd-imports.create') }}" class="group bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg p-6 text-center transition duration-200 transform hover:scale-105">
                <div class="bg-blue-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                    <i class="fas fa-file-import text-2xl text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Importar Arquivo AFD</h3>
                <p class="text-sm text-gray-600">Envie arquivos de ponto eletrônico</p>
            </a>

            <a href="{{ route('timesheets.index') }}" class="group bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-6 text-center transition duration-200 transform hover:scale-105">
                <div class="bg-green-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                    <i class="fas fa-file-alt text-2xl text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Gerar Relatório</h3>
                <p class="text-sm text-gray-600">Cartões de ponto em lote</p>
            </a>

            <a href="{{ route('employees.create') }}" class="group bg-purple-50 hover:bg-purple-100 border-2 border-purple-200 rounded-lg p-6 text-center transition duration-200 transform hover:scale-105">
                <div class="bg-purple-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                    <i class="fas fa-user-plus text-2xl text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Adicionar Colaborador</h3>
                <p class="text-sm text-gray-600">Cadastre novo funcionário</p>
            </a>

            <a href="{{ route('employee-imports.create') }}" class="group bg-orange-50 hover:bg-orange-100 border-2 border-orange-200 rounded-lg p-6 text-center transition duration-200 transform hover:scale-105">
                <div class="bg-orange-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                    <i class="fas fa-upload text-2xl text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Importar Planilha</h3>
                <p class="text-sm text-gray-600">Upload de colaboradores CSV</p>
            </a>
        </div>
    </div>

    <!-- Atividade Recente -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Atividade Recente</h2>
            <a href="{{ route('afd-imports.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Ver todas <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        @php
            $recentImports = \App\Models\AfdImport::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        @endphp

        @if($recentImports->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Arquivo</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Modelo do Relógio</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Usuário</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Data/Hora</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentImports as $import)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-4 px-4">
                            <div class="flex items-center">
                                <i class="fas fa-file text-gray-400 mr-3"></i>
                                <span class="font-medium text-gray-900">{{ Str::limit($import->file_name, 40) }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-gray-600">
                            {{ $import->format_type ?? 'N/A' }}
                        </td>
                        <td class="py-4 px-4 text-gray-600">
                            {{ $import->user ? $import->user->name : 'Sistema' }}
                        </td>
                        <td class="py-4 px-4 text-gray-600">
                            {{ $import->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($import->status === 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Concluído
                                </span>
                            @elseif($import->status === 'processing')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Em Processamento
                                </span>
                            @elseif($import->status === 'failed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Falha
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-clock mr-1"></i>Pendente
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500 text-lg">Nenhuma importação realizada ainda</p>
            <a href="{{ route('afd-imports.create') }}" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                Fazer primeira importação <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
