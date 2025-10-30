<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nova Importação - Sistema de Ponto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                            Sistema de Ponto
                        </a>
                        <span class="ml-3 text-gray-500">→</span>
                        <a href="{{ route('employee-imports.index') }}" class="ml-3 text-gray-600 hover:text-gray-900">
                            Importações
                        </a>
                        <span class="ml-3 text-gray-500">→</span>
                        <span class="ml-3 text-gray-900 font-medium">Nova Importação</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Nova Importação de Colaboradores</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Faça upload de um arquivo CSV para importar colaboradores em massa.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Erros encontrados:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Download Template -->
            <div class="mb-6">
                <a href="{{ route('employee-imports.template') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Baixar Modelo CSV
                </a>
            </div>

            <!-- Upload Form -->
            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('employee-imports.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-6">
                            <!-- File Upload -->
                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700">
                                    Arquivo CSV
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors" id="dropZone">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Clique para selecionar</span>
                                                <input id="file" name="csv_file" type="file" accept=".csv" class="sr-only" required>
                                            </label>
                                            <p class="pl-1">ou arraste e solte</p>
                                        </div>
                                        <p class="text-xs text-gray-500">CSV até 10MB</p>
                                        <p id="fileName" class="text-sm font-medium text-gray-900 mt-2"></p>
                                    </div>
                                </div>
                                @error('csv_file')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Preview Area -->
                            <div id="previewArea" class="hidden">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Pré-visualização</h3>
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div id="previewContent" class="space-y-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                        <div class="flex gap-3 justify-end">
                            <a href="{{ route('employee-imports.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <span id="submitBtnText">Importar Colaboradores</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('fileName');
        const dropZone = document.getElementById('dropZone');
        const submitBtn = document.getElementById('submitBtn');
        const previewArea = document.getElementById('previewArea');

        // File input change handler
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                submitBtn.disabled = false;
                readAndPreviewFile(this.files[0]);
            }
        });

        // Drag and drop handlers
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].name.endsWith('.csv')) {
                fileInput.files = files;
                fileName.textContent = files[0].name;
                submitBtn.disabled = false;
                readAndPreviewFile(files[0]);
            }
        });

        // Read and preview file
        function readAndPreviewFile(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const text = e.target.result;
                const lines = text.split('\n').slice(0, 4); // Show first 3 rows
                
                previewArea.classList.remove('hidden');
                document.getElementById('previewContent').innerHTML = `
                    <p class="text-sm text-gray-600 mb-2">Primeiras linhas do arquivo:</p>
                    <pre class="text-xs bg-white p-2 rounded border overflow-x-auto">${lines.join('\n')}</pre>
                    <p class="text-sm text-gray-600 mt-2">Total de linhas: ${text.split('\n').length - 1}</p>
                    <p class="text-sm text-blue-600 mt-2 font-medium">✓ Arquivo carregado! Clique em "Importar Colaboradores" para processar.</p>
                `;
            };
            reader.readAsText(file);
        }

        // Form submit handler
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            document.getElementById('submitBtnText').textContent = 'Importando...';
        });
    </script>
</body>
</html>
