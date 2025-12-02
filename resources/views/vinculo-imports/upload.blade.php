@extends('layouts.main')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-file-import text-blue-600 mr-3"></i>Importação de Vínculos e Jornadas
            </h1>
            <p class="text-gray-600 mt-2">Importe vínculos de colaboradores e associe jornadas de trabalho a partir de arquivo CSV legado</p>
        </div>
        <a href="{{ route('vinculo-imports.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg">
            <i class="fas fa-list mr-2"></i>Ver Histórico
        </a>
    </div>

    <!-- Card de Upload -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">
            <i class="fas fa-upload text-blue-600 mr-2"></i>Upload de Arquivo CSV
        </h2>

        <form action="{{ route('vinculo-imports.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-file-csv text-blue-600 mr-2"></i>Selecione o arquivo CSV
                </label>
                
                <input 
                    type="file" 
                    name="csv_file" 
                    accept=".csv,.txt"
                    required
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500 p-3"
                >
                
                @error('csv_file')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg shadow-lg hover:shadow-xl transition flex items-center">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Iniciar Importação
                </button>
                
                <a href="{{ route('vinculo-imports.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-8 py-3 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>

    <!-- Card de Instruções -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-900 mb-4">
            <i class="fas fa-info-circle mr-2"></i>Formato do Arquivo CSV
        </h3>

        <div class="space-y-4">
            <div>
                <h4 class="font-semibold text-blue-800 mb-2">Colunas Obrigatórias:</h4>
                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                    <li><strong>NOME</strong>: Nome completo da pessoa</li>
                    <li><strong>Nº PIS/PASEP</strong>: Identificador único da pessoa (usado para buscar/criar)</li>
                    <li><strong>Nº IDENTIFICADOR</strong>: Número da matrícula do vínculo (único por vínculo)</li>
                    <li><strong>HORÁRIO</strong>: Campo contendo o ID da jornada (ex: "7 - SAÚDE -07:30-11:30...")</li>
                    <li><strong>HORÁRIO_LIMPO</strong>: Descrição textual da jornada (opcional)</li>
                </ul>
            </div>

            <div class="bg-white rounded p-4 border border-blue-200">
                <h4 class="font-semibold text-blue-800 mb-2">Exemplo de Linha:</h4>
                <code class="text-xs text-gray-600 block overflow-x-auto">
                    João Silva,12345678901,M001,"7 - SAÚDE -07:30-11:30-13:00-17:00","7h/dia"
                </code>
            </div>

            <div>
                <h4 class="font-semibold text-blue-800 mb-2">Lógica de Importação:</h4>
                <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                    <li>Sistema busca ou cria a <strong>Pessoa</strong> pelo PIS/PASEP</li>
                    <li>Sistema busca ou cria o <strong>Vínculo</strong> pela Matrícula</li>
                    <li>Sistema extrai o <strong>ID da Jornada</strong> do campo HORÁRIO</li>
                    <li>Sistema associa a <strong>Jornada ao Vínculo</strong></li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Card de Avisos -->
    <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6">
        <h3 class="text-lg font-bold text-yellow-900 mb-3">
            <i class="fas fa-exclamation-triangle mr-2"></i>Avisos Importantes
        </h3>
        <ul class="list-disc list-inside text-sm text-gray-700 space-y-2">
            <li>O arquivo será processado em <strong>segundo plano (fila)</strong></li>
            <li>Vínculos duplicados (mesma matrícula) serão <strong>atualizados</strong>, não duplicados</li>
            <li>Se o ID da jornada não existir, o vínculo será criado mas <strong>sem jornada associada</strong></li>
            <li>Tamanho máximo do arquivo: <strong>10MB</strong></li>
            <li>O processo pode levar alguns minutos dependendo do tamanho do arquivo</li>
        </ul>
    </div>
</div>
@endsection
