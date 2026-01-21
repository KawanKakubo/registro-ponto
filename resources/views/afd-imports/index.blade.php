@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-file-import text-blue-600 mr-3"></i>Importações AFD
            </h1>
            <p class="text-gray-600 mt-2">Histórico de importações de arquivos AFD</p>
        </div>
        <a href="{{ route('afd-imports.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition">
            <i class="fas fa-plus mr-2"></i>Nova Importação
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Total</p>
                    <p class="text-3xl font-bold">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-file-import text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Concluídas</p>
                    <p class="text-3xl font-bold">{{ $stats['completed'] }}</p>
                </div>
                <div class="bg-green-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-check-circle text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-900 text-sm font-medium mb-1">Aguardando Revisão</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['pending_review'] ?? 0 }}</p>
                </div>
                <div class="bg-yellow-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-user-plus text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Processando</p>
                    <p class="text-3xl font-bold">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-orange-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-clock text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium mb-1">Com Erro</p>
                    <p class="text-3xl font-bold">{{ $stats['failed'] }}</p>
                </div>
                <div class="bg-red-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-exclamation-circle text-2xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Arquivo</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Importado Por</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Data/Hora</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Registros</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Status</th>
                        <th class="text-right px-6 py-4 font-semibold text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($imports as $import)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                                            <div>
                                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white mr-3 shadow">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $import->file_name }}</p>
                                    @if($import->format_type)
                                        <p class="text-xs text-gray-500">{{ ucfirst($import->format_type) }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-user text-gray-400 mr-2"></i>
                                {{ $import->importedByUser->full_name ?? 'Sistema' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                {{ $import->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-list-ol mr-2"></i>
                                {{ number_format($import->total_records, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($import->status == 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>Concluída
                                </span>
                            @elseif($import->status == 'failed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-2"></i>Erro
                                </span>
                            @elseif($import->status == 'pending_review')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Revisão ({{ $import->pending_count }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-clock mr-2"></i>Processando
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end space-x-2">
                                @if($import->status == 'pending_review')
                                    <a href="{{ route('afd-imports.review', $import) }}" class="text-yellow-600 hover:text-yellow-800 p-2 hover:bg-yellow-50 rounded transition" title="Revisar pendentes">
                                        <i class="fas fa-user-plus"></i>
                                    </a>
                                @endif
                                <a href="{{ route('afd-imports.show', $import) }}" class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded transition" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <i class="fas fa-file-import text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">Nenhuma importação realizada</p>
                            <a href="{{ route('afd-imports.create') }}" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-plus mr-2"></i>Realizar primeira importação
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
