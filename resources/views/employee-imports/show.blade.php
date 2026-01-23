@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employee-imports.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-file-import text-blue-600 mr-3"></i>Detalhes da Importação #{{ $import->id }}
            </h1>
            <p class="text-gray-600 mt-2">{{ $import->original_filename }}</p>
        </div>
        
        @if($import->status === 'completed')
        <a href="{{ route('employees.index') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg">
            <i class="fas fa-users mr-2"></i>Ver Colaboradores
        </a>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-gray-100 mr-4">
                    <i class="fas fa-list text-2xl text-gray-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total de Linhas</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $import->total_rows }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-white/20 mr-4">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium opacity-90">Criados</p>
                    <p class="text-3xl font-bold">{{ $import->success_count }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-white/20 mr-4">
                    <i class="fas fa-sync text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium opacity-90">Atualizados</p>
                    <p class="text-3xl font-bold">{{ $import->updated_count }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-white/20 mr-4">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium opacity-90">Erros</p>
                    <p class="text-3xl font-bold">{{ $import->error_count }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar for Processing -->
    @if($import->status === 'processing')
    <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-6 mb-6">
        <div class="flex items-center mb-4">
            <i class="fas fa-spinner fa-spin text-blue-600 text-2xl mr-4"></i>
            <div>
                <h3 class="text-lg font-bold text-blue-900">Processamento em andamento...</h3>
                <p class="text-sm text-blue-700">A página será atualizada automaticamente</p>
            </div>
        </div>
        <div class="w-full bg-blue-200 rounded-full h-3">
            <div class="bg-blue-600 h-3 rounded-full animate-pulse transition-all duration-500" style="width: 50%"></div>
        </div>
    </div>
    @endif

    <!-- Error Message -->
    @if($import->error_message)
    <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-6 mb-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-4 mt-1"></i>
            <div class="flex-1">
                <h3 class="font-bold text-red-900 mb-2">Mensagem de Erro</h3>
                <pre class="text-sm text-red-800 whitespace-pre-wrap bg-white p-4 rounded border border-red-200 font-mono overflow-x-auto">{{ $import->error_message }}</pre>
            </div>
        </div>
    </div>
    @endif

    <!-- Error Details Section -->
    @if($import->error_count > 0 && !empty($errorDetails))
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-red-900 flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                        Detalhes dos Erros ({{ count($errorDetails) }} {{ count($errorDetails) === 1 ? 'linha' : 'linhas' }})
                    </h3>
                    <p class="text-sm text-red-700 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Linhas que não puderam ser importadas
                    </p>
                </div>
                <a href="{{ route('employee-imports.errors', $import) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-lg">
                    <i class="fas fa-list-alt mr-2"></i>Ver Página Completa de Erros
                </a>
            </div>
        </div>
        
        <div class="p-6">
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @foreach($errorDetails as $error)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-red-600 text-white font-bold px-3 py-1 rounded-lg text-sm shadow">
                                Linha {{ $error['line'] }}
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-sm font-semibold text-red-900 mb-2">
                                {{ count($error['errors']) }} {{ count($error['errors']) === 1 ? 'erro encontrado' : 'erros encontrados' }}:
                            </h4>
                            <ul class="space-y-1">
                                @foreach($error['errors'] as $errorMessage)
                                <li class="text-sm text-red-700 flex items-start">
                                    <i class="fas fa-times-circle mr-2 mt-0.5 flex-shrink-0"></i>
                                    <span>{{ $errorMessage }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if(count($errorDetails) > 10)
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Mostrando todos os {{ count($errorDetails) }} erros. Role para ver mais.
                </p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Import Details -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Informações da Importação
            </h3>
        </div>
        <div class="divide-y divide-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 py-4 hover:bg-gray-50">
                <dt class="text-sm font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-file text-gray-400 mr-2"></i>Arquivo
                </dt>
                <dd class="text-sm text-gray-900 md:col-span-2 font-mono">{{ $import->original_filename }}</dd>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 py-4 hover:bg-gray-50">
                <dt class="text-sm font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-weight text-gray-400 mr-2"></i>Tamanho
                </dt>
                <dd class="text-sm text-gray-900 md:col-span-2">{{ number_format($import->file_size / 1024, 2) }} KB</dd>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 py-4 hover:bg-gray-50">
                <dt class="text-sm font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-flag text-gray-400 mr-2"></i>Status
                </dt>
                <dd class="text-sm text-gray-900 md:col-span-2">
                    @if($import->status === 'pending')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-2"></i>Pendente
                        </span>
                    @elseif($import->status === 'processing')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Processando
                        </span>
                    @elseif($import->status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-2"></i>Concluído
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-2"></i>Falhou
                        </span>
                    @endif
                </dd>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 py-4 hover:bg-gray-50">
                <dt class="text-sm font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-calendar-plus text-gray-400 mr-2"></i>Data de Upload
                </dt>
                <dd class="text-sm text-gray-900 md:col-span-2">{{ $import->created_at->format('d/m/Y H:i:s') }}</dd>
            </div>

            @if($import->processed_at)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 py-4 hover:bg-gray-50">
                <dt class="text-sm font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-calendar-check text-gray-400 mr-2"></i>Data de Processamento
                </dt>
                <dd class="text-sm text-gray-900 md:col-span-2">{{ $import->processed_at->format('d/m/Y H:i:s') }}</dd>
            </div>
            @endif

            @if($import->processed_at && $import->created_at)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 py-4 hover:bg-gray-50">
                <dt class="text-sm font-semibold text-gray-700 flex items-center">
                    <i class="fas fa-hourglass-half text-gray-400 mr-2"></i>Tempo de Processamento
                </dt>
                <dd class="text-sm text-gray-900 md:col-span-2">
                    {{ $import->created_at->diffForHumans($import->processed_at, true) }}
                </dd>
            </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between pt-6">
        <a href="{{ route('employee-imports.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left mr-2"></i>Voltar para Listagem
        </a>
    </div>
</div>

<!-- Auto-refresh for processing imports -->
@if($import->status === 'processing' || $import->status === 'pending')
<script>
    setTimeout(() => {
        window.location.reload();
    }, 3000);
</script>
@endif
@endsection
