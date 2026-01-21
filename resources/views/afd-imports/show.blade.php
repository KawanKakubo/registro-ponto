@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center flex-1">
            <a href="{{ route('afd-imports.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-file-import text-blue-600 mr-3"></i>Detalhes da Importação AFD
                    @if($afdImport->status === 'completed')
                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Concluída
                        </span>
                    @elseif($afdImport->status === 'failed')
                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>Falhou
                        </span>
                    @elseif($afdImport->status === 'pending_review')
                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Aguardando Revisão
                        </span>
                    @else
                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>{{ ucfirst($afdImport->status) }}
                        </span>
                    @endif
                </h1>
                <p class="text-gray-600 mt-2">
                    <i class="fas fa-file mr-1"></i>{{ $afdImport->file_name }}
                </p>
            </div>
        </div>
    </div>

    <!-- Import Info -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Informações da Importação
            </h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <div class="flex items-start">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-file text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Arquivo</p>
                    <p class="font-semibold text-gray-900 font-mono">{{ $afdImport->file_name }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-gray-100 rounded-lg mr-3">
                    <i class="fas fa-flag text-gray-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    @if($afdImport->status === 'completed')
                        <span class="inline-flex items-center px-2 py-1 rounded text-sm font-semibold bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Concluída
                        </span>
                    @elseif($afdImport->status === 'failed')
                        <span class="inline-flex items-center px-2 py-1 rounded text-sm font-semibold bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>Falhou
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded text-sm font-semibold bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>{{ ucfirst($afdImport->status) }}
                        </span>
                    @endif
                </div>
            </div>

            @if($afdImport->format_type)
            <div class="flex items-start">
                <div class="p-2 bg-purple-100 rounded-lg mr-3">
                    <i class="fas fa-clock text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Modelo do Relógio</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                        <i class="fas fa-microchip mr-1"></i>{{ $afdImport->format_type }}
                    </span>
                </div>
            </div>
            @endif

            <div class="flex items-start">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-calendar text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Data da Importação</p>
                    <p class="font-semibold text-gray-900">{{ $afdImport->imported_at?->format('d/m/Y H:i:s') ?? $afdImport->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                    <i class="fas fa-weight text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tamanho do Arquivo</p>
                    <p class="font-semibold text-gray-900">{{ number_format($afdImport->file_size / 1024, 2) }} KB</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-indigo-100 rounded-lg mr-3">
                    <i class="fas fa-list text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total de Registros</p>
                    <p class="font-semibold text-gray-900 text-2xl">{{ $afdImport->total_records ?? 0 }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-id-card text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">CNPJ</p>
                    <p class="font-semibold text-gray-900 font-mono">{{ $afdImport->cnpj ?? '-' }}</p>
                </div>
            </div>

            @if($afdImport->start_date && $afdImport->end_date)
            <div class="flex items-start md:col-span-2">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-calendar-alt text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Período</p>
                    <p class="font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($afdImport->start_date)->format('d/m/Y') }} 
                        <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                        {{ \Carbon\Carbon::parse($afdImport->end_date)->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Error Message -->
    @if($afdImport->error_message)
    <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-6 mb-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-4 mt-1"></i>
            <div class="flex-1">
                <h3 class="font-bold text-red-900 mb-2">Mensagem de Erro</h3>
                <pre class="text-sm text-red-800 whitespace-pre-wrap bg-white p-4 rounded border border-red-200 font-mono overflow-x-auto">{{ $afdImport->error_message }}</pre>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Review Alert -->
    @if($afdImport->hasPendingEmployees())
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
        <div class="flex items-start">
            <i class="fas fa-user-plus text-yellow-600 text-2xl mr-4 mt-1"></i>
            <div class="flex-1">
                <h3 class="font-bold text-yellow-900 mb-2">
                    {{ $afdImport->pending_count }} Colaborador(es) Não Encontrado(s)
                </h3>
                <p class="text-sm text-yellow-800 mb-4">
                    Alguns registros de ponto não puderam ser importados porque os colaboradores não estão cadastrados no sistema.
                    Você pode cadastrá-los agora ou ignorar esses registros.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('afd-imports.review', $afdImport) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition shadow-lg">
                        <i class="fas fa-user-plus mr-2"></i>Revisar Colaboradores Pendentes
                    </a>
                    <form action="{{ route('afd-imports.skip-all', $afdImport) }}" method="POST" class="inline"
                          onsubmit="return confirm('Tem certeza que deseja ignorar todos os {{ $afdImport->pending_count }} colaboradores pendentes? Os registros de ponto deles NÃO serão importados.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                            <i class="fas fa-forward mr-2"></i>Ignorar Todos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Success Message -->
    @if($afdImport->status === 'completed' && !$afdImport->error_message)
    <div class="bg-green-50 border-l-4 border-green-600 rounded-lg p-6">
        <div class="flex items-start">
            <i class="fas fa-check-circle text-green-600 text-2xl mr-4 mt-1"></i>
            <div class="flex-1">
                <h3 class="font-bold text-green-900 mb-2">Importação Concluída com Sucesso</h3>
                <p class="text-sm text-green-800 mb-4">Este arquivo foi processado com sucesso. Os registros estão disponíveis na listagem de colaboradores.</p>
                <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                    <i class="fas fa-users mr-2"></i>Ver Colaboradores
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
