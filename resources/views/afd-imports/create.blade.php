@extends('layouts.main')

@section('title', 'Importar Arquivo AFD')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-4 mb-4">
            <a href="{{ route('afd-imports.index') }}" class="text-gray-600 hover:text-gray-900 transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Importar Arquivo AFD</h1>
                <p class="text-gray-600 mt-1">Envie arquivos de ponto eletrônico para processamento</p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        <form action="{{ route('afd-imports.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Envie o Arquivo AFD -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-file-upload text-blue-600 mr-2"></i>
                    Importar Arquivo AFD
                </h2>

                <!-- Drop Zone -->
                <div 
                    x-data="{ 
                        fileName: '',
                        isDragging: false,
                        handleFiles(files) {
                            if (files.length > 0) {
                                this.fileName = files[0].name;
                                document.getElementById('afd_file').files = files;
                            }
                        }
                    }"
                    @drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
                    class="border-2 border-dashed rounded-lg p-12 text-center transition"
                >
                    <input 
                        type="file" 
                        name="afd_file" 
                        id="afd_file" 
                        accept=".txt"
                        class="sr-only"
                        @change="fileName = $event.target.files[0]?.name || ''"
                        required
                    >

                    <div x-show="!fileName">
                        <i class="fas fa-cloud-upload-alt text-6xl text-gray-300 mb-4"></i>
                        <p class="text-lg font-semibold text-gray-700 mb-2">Arraste e solte o arquivo AFD aqui</p>
                        <p class="text-sm text-gray-500 mb-4">ou</p>
                        <label for="afd_file" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg cursor-pointer transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-folder-open mr-2"></i>Selecionar do Computador
                        </label>
                        <p class="text-xs text-gray-400 mt-4">Formato aceito: .txt (AFD - Portaria 671/2021)</p>
                    </div>

                    <div x-show="fileName" x-cloak>
                        <i class="fas fa-file-alt text-6xl text-green-500 mb-4"></i>
                        <p class="text-lg font-semibold text-gray-700 mb-2" x-text="fileName"></p>
                        <p class="text-sm text-green-600 mb-4">
                            <i class="fas fa-check-circle mr-1"></i>Arquivo selecionado
                        </p>
                        <label for="afd_file" class="inline-block text-blue-600 hover:text-blue-800 font-medium cursor-pointer">
                            <i class="fas fa-sync mr-1"></i>Escolher outro arquivo
                        </label>
                    </div>
                </div>

                @error('afd_file')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Informação Importante -->
            <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h3 class="font-bold text-blue-900 mb-2">Informações Importantes</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li><i class="fas fa-check mr-2"></i>O arquivo AFD deve estar no formato especificado pela Portaria 671/2021</li>
                            <li><i class="fas fa-check mr-2"></i>Todos os colaboradores devem estar previamente cadastrados no sistema</li>
                            <li><i class="fas fa-check mr-2"></i>O processamento será realizado em segundo plano</li>
                            <li><i class="fas fa-check mr-2"></i>Você será notificado quando a importação for concluída</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('afd-imports.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button 
                    type="submit" 
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl"
                >
                    <i class="fas fa-upload mr-2"></i>Enviar Arquivo AFD
                </button>
            </div>
        </form>
    </div>

    <!-- Atividade Recente -->
    @php
        $recentImports = \App\Models\AfdImport::orderBy('created_at', 'desc')->take(5)->get();
    @endphp

    @if($recentImports->count() > 0)
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
            <i class="fas fa-history mr-2 text-gray-600"></i>
            Importações Recentes
        </h2>

        <div class="space-y-3">
            @foreach($recentImports as $import)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="flex items-center flex-1">
                    <i class="fas fa-file text-gray-400 text-2xl mr-4"></i>
                    <div>
                        <p class="font-medium text-gray-900">{{ Str::limit($import->file_name, 50) }}</p>
                        <p class="text-sm text-gray-500">{{ $import->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div>
                    @if($import->status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Concluído
                        </span>
                    @elseif($import->status === 'processing')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-spinner fa-spin mr-1"></i>Processando
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
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
