@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-history text-blue-600 mr-3"></i>Histórico de Importações
            </h1>
            <p class="text-gray-600 mt-2">Visualize todas as importações de vínculos e jornadas realizadas</p>
        </div>
        <a href="{{ route('vinculo-imports.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition">
            <i class="fas fa-plus-circle mr-2"></i>Nova Importação
        </a>
    </div>

    @if($imports->count() > 0)
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <tr>
                    <th class="text-left px-6 py-3 font-medium">Arquivo</th>
                    <th class="text-center px-6 py-3 font-medium">Status</th>
                    <th class="text-center px-6 py-3 font-medium">Linhas</th>
                    <th class="text-center px-6 py-3 font-medium">Pessoas</th>
                    <th class="text-center px-6 py-3 font-medium">Vínculos</th>
                    <th class="text-center px-6 py-3 font-medium">Jornadas</th>
                    <th class="text-center px-6 py-3 font-medium">Erros</th>
                    <th class="text-center px-6 py-3 font-medium">Data</th>
                    <th class="text-center px-6 py-3 font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($imports as $import)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $import->filename }}</div>
                        @if($import->user)
                            <div class="text-sm text-gray-500">Por: {{ $import->user->name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($import->status === 'completed')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Concluída
                            </span>
                        @elseif($import->status === 'processing')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                <i class="fas fa-spinner fa-spin mr-1"></i>Processando
                            </span>
                        @elseif($import->status === 'pending')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                <i class="fas fa-clock mr-1"></i>Pendente
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>Falhou
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-lg font-semibold text-gray-700">{{ number_format($import->total_linhas, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="text-sm">
                            <span class="text-green-600 font-semibold">{{ $import->pessoas_criadas }}</span>
                            <span class="text-gray-400 mx-1">/</span>
                            <span class="text-blue-600 font-semibold">{{ $import->pessoas_atualizadas }}</span>
                        </div>
                        <div class="text-xs text-gray-500">criadas/atualiz.</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="text-sm">
                            <span class="text-green-600 font-semibold">{{ $import->vinculos_criados }}</span>
                            <span class="text-gray-400 mx-1">/</span>
                            <span class="text-blue-600 font-semibold">{{ $import->vinculos_atualizados }}</span>
                        </div>
                        <div class="text-xs text-gray-500">criados/atualiz.</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-lg font-semibold text-purple-600">{{ $import->jornadas_associadas }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($import->erros > 0)
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                {{ $import->erros }}
                            </span>
                        @else
                            <span class="text-gray-400">0</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                        {{ $import->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('vinculo-imports.show', $import) }}" 
                               class="text-blue-600 hover:text-blue-800" 
                               title="Ver Detalhes">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($import->erros > 0)
                                <a href="{{ route('vinculo-imports.errors', $import) }}" 
                                   class="text-red-600 hover:text-red-800" 
                                   title="Ver Erros">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </a>
                            @endif
                            <a href="{{ route('vinculo-imports.download', $import) }}" 
                               class="text-green-600 hover:text-green-800" 
                               title="Download CSV">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $imports->links() }}
    </div>
    @else
    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500 text-lg mb-6">Nenhuma importação realizada ainda</p>
        <a href="{{ route('vinculo-imports.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg inline-block">
            <i class="fas fa-plus-circle mr-2"></i>Realizar Primeira Importação
        </a>
    </div>
    @endif
</div>
@endsection
