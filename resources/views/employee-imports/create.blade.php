<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Importação - Sistema de Ponto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Nova Importação de Colaboradores</h1>
                <a href="{{ route('employee-imports.index') }}" class="text-blue-600 hover:text-blue-800">← Voltar</a>
            </div>
        </header>

        <main class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Instructions -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Instruções para Importação</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Baixe o modelo CSV clicando no botão abaixo</li>
                                <li>Preencha os dados dos colaboradores no arquivo</li>
                                <li>CPF e PIS/PASEP devem conter apenas 11 dígitos numéricos</li>
                                <li>Data de admissão deve estar no formato YYYY-MM-DD</li>
                                <li>Status deve ser "ativo" ou "inativo"</li>
                                <li>Colaboradores com CPF já existente serão atualizados</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download Template Button -->
            <div class="mb-6">
                <a href="/modelo-importacao-colaboradores.csv" download class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded inline-flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Baixar Modelo CSV
                </a>
            </div>

            <!-- Upload Form -->
            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('employee-imports.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
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
                                                <input id="file" name="file" type="file" accept=".csv" class="sr-only" required>
                                            </label>
                                            <p class="pl-1">ou arraste e solte</p>
                                        </div>
                                        <p class="text-xs text-gray-500">CSV até 10MB</p>
                                        <p id="fileName" class="text-sm font-medium text-gray-900 mt-2"></p>
                                    </div>
                                </div>
                                @error('file')
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

                            <!-- Validation Results -->
                            <div id="validationResults" class="hidden">
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-3">Resultado da Validação</h3>
                                    <div id="validationContent"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-between items-center">
                        <button type="button" id="validateBtn" class="hidden bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            Validar Arquivo
                        </button>
                        <div class="flex gap-3">
                            <a href="{{ route('employee-imports.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Importar Colaboradores
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
        const validateBtn = document.getElementById('validateBtn');
        const submitBtn = document.getElementById('submitBtn');
        const previewArea = document.getElementById('previewArea');
        const validationResults = document.getElementById('validationResults');

        // File selection handler
        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                validateBtn.classList.remove('hidden');
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
                validateBtn.classList.remove('hidden');
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
                `;
            };
            reader.readAsText(file);
        }

        // Validate button handler
        validateBtn.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            this.disabled = true;
            this.textContent = 'Validando...';

            fetch('{{ route('employee-imports.validate') }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                validationResults.classList.remove('hidden');
                
                if (data.valid) {
                    document.getElementById('validationContent').innerHTML = `
                        <div class="bg-green-50 border border-green-200 rounded p-3">
                            <p class="text-green-800 font-medium">✓ Arquivo válido!</p>
                            <ul class="mt-2 text-sm text-green-700 space-y-1">
                                <li>• Total de registros: ${data.total_rows}</li>
                                <li>• Registros válidos: ${data.valid_rows}</li>
                            </ul>
                        </div>
                    `;
                    submitBtn.disabled = false;
                } else {
                    document.getElementById('validationContent').innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded p-3">
                            <p class="text-red-800 font-medium">✗ Erros encontrados:</p>
                            <ul class="mt-2 text-sm text-red-700 space-y-1">
                                <li>• Total de registros: ${data.total_rows}</li>
                                <li>• Registros válidos: ${data.valid_rows}</li>
                                <li>• Registros inválidos: ${data.invalid_rows}</li>
                            </ul>
                            <div class="mt-3">
                                <p class="font-medium text-red-800">Erros:</p>
                                <ul class="mt-1 text-xs text-red-600 space-y-1">
                                    ${data.errors.slice(0, 10).map(err => `<li>• ${err}</li>`).join('')}
                                    ${data.errors.length > 10 ? `<li class="font-medium">... e mais ${data.errors.length - 10} erros</li>` : ''}
                                </ul>
                            </div>
                        </div>
                    `;
                    submitBtn.disabled = true;
                }
                
                validateBtn.disabled = false;
                validateBtn.textContent = 'Validar Arquivo';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao validar arquivo. Tente novamente.');
                validateBtn.disabled = false;
                validateBtn.textContent = 'Validar Arquivo';
            });
        });

        // Form submit handler
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Importando...';
        });
    </script>
</body>
</html>
