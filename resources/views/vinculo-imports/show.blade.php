@extends('layouts.main')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-file-alt text-blue-600 mr-3"></i>Detalhes da Importação
            </h1>
            <p class="text-gray-600 mt-2">{{ $import->filename }}</p>
        </div>
        <a href="{{ route('vinculo-imports.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg">
            <i class="fas fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    <!-- Card de Status -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Status da Importação
            </h2>
            @if($import->status === 'completed')
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i>Concluída
                </span>
            @elseif($import->status === 'processing')
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                    <i class="fas fa-spinner fa-spin mr-1"></i>Processando...
                </span>
            @elseif($import->status === 'pending')
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                    <i class="fas fa-clock mr-1"></i>Pendente
                </span>
            @else
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                    <i class="fas fa-times-circle mr-1"></i>Falhou
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600 mb-1">Arquivo</p>
                <p class="font-semibold text-gray-900">{{ $import->filename }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Enviado por</p>
                <p class="font-semibold text-gray-900">{{ $import->user->name ?? 'Sistema' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Data</p>
                <p class="font-semibold text-gray-900">{{ $import->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @if($import->started_at)
            <div class="mt-4 pt-4 border-t grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Iniciado em</p>
                    <p class="font-semibold text-gray-900">{{ $import->started_at->format('d/m/Y H:i:s') }}</p>
                </div>
                @if($import->completed_at)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Concluído em</p>
                        <p class="font-semibold text-gray-900">{{ $import->completed_at->format('d/m/Y H:i:s') }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Tempo: {{ $import->started_at->diffForHumans($import->completed_at, true) }}
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Estatísticas -->
    @if($import->isCompleted())
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total de Linhas -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-1">Total de Linhas</p>
                    <p class="text-4xl font-bold">{{ number_format($import->total_linhas, 0, ',', '.') }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-4">
                    <i class="fas fa-list-ol text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Taxa de Sucesso -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm mb-1">Taxa de Sucesso</p>
                    <p class="text-4xl font-bold">{{ number_format($import->success_rate, 1) }}%</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-4">
                    <i class="fas fa-chart-line text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Erros -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm mb-1">Erros</p>
                    <p class="text-4xl font-bold">{{ $import->erros }}</p>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-full p-4">
                    <i class="fas fa-exclamation-triangle text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados Detalhados -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Pessoas -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-users text-blue-600 mr-2"></i>Pessoas
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Criadas</span>
                    <span class="font-bold text-green-600 text-xl">{{ $import->pessoas_criadas }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Atualizadas</span>
                    <span class="font-bold text-blue-600 text-xl">{{ $import->pessoas_atualizadas }}</span>
                </div>
                <div class="pt-3 border-t">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-700">Total Processadas</span>
                        <span class="font-bold text-gray-900 text-xl">{{ $import->pessoas_criadas + $import->pessoas_atualizadas }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vínculos -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-id-card text-purple-600 mr-2"></i>Vínculos
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Criados</span>
                    <span class="font-bold text-green-600 text-xl">{{ $import->vinculos_criados }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Atualizados</span>
                    <span class="font-bold text-blue-600 text-xl">{{ $import->vinculos_atualizados }}</span>
                </div>
                <div class="pt-3 border-t">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-700">Total Processados</span>
                        <span class="font-bold text-gray-900 text-xl">{{ $import->vinculos_criados + $import->vinculos_atualizados }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jornadas Associadas -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-business-time text-indigo-600 mr-2"></i>Jornadas de Trabalho
        </h3>
        <div class="flex items-center justify-between">
            <p class="text-gray-600">Total de jornadas associadas aos vínculos</p>
            <span class="font-bold text-indigo-600 text-3xl">{{ $import->jornadas_associadas }}</span>
        </div>
    </div>
    @endif

    <!-- Ações -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-tools text-blue-600 mr-2"></i>Ações
        </h3>
        <div class="flex gap-3">
            <a href="{{ route('vinculo-imports.download', $import) }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-download mr-2"></i>Download CSV Original
            </a>
            
            @if($import->erros > 0)
                <a href="{{ route('vinculo-imports.errors', $import) }}" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Ver Erros Detalhados
                </a>
                
                <a href="{{ route('vinculo-imports.download-errors', $import) }}" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-file-csv mr-2"></i>Download Relatório de Erros
                </a>
            @endif
        </div>
    </div>
</div>

@if($import->isProcessing())
<script>
    // Auto-refresh a cada 5 segundos enquanto processando
    setTimeout(() => {
        location.reload();
    }, 5000);
</script>
@endif
@endsection
