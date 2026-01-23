@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>Erros da Importação
            </h1>
            <p class="text-gray-600 mt-2">{{ $import->filename }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('vinculo-imports.download-errors', $import) }}" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg">
                <i class="fas fa-download mr-2"></i>Download Erros (CSV)
            </a>
            <a href="{{ route('vinculo-imports.show', $import) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </div>

    <!-- Resumo de Erros -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-red-900 mb-2">
                    <i class="fas fa-bug mr-2"></i>{{ count($errors) }} Erros Encontrados
                </h2>
                <p class="text-red-700">
                    De {{ $import->total_linhas }} linhas processadas, 
                    <strong>{{ count($errors) }}</strong> apresentaram erros 
                    (<strong>{{ number_format((count($errors) / $import->total_linhas) * 100, 1) }}%</strong>)
                </p>
            </div>
            <div class="bg-red-600 text-white rounded-full w-20 h-20 flex items-center justify-center">
                <span class="text-3xl font-bold">{{ count($errors) }}</span>
            </div>
        </div>
    </div>

    <!-- Filtros e Pesquisa -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Pesquisar por nome, PIS, matrícula..." 
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <button 
                onclick="clearSearch()" 
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Limpar
            </button>
        </div>
        <p class="text-sm text-gray-500 mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            <span id="errorCount">{{ count($errors) }}</span> erro(s) exibido(s)
        </p>
    </div>

    <!-- Lista de Erros -->
    <div class="space-y-4" id="errorList">
        @foreach($errors as $error)
        <div class="error-item bg-white rounded-lg shadow-lg overflow-hidden border-l-4 border-red-500" data-line="{{ $error['line'] }}">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-file-alt text-red-600 mr-2"></i>Linha {{ $error['line'] }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Clique abaixo para ver os detalhes completos</p>
                    </div>
                    <button 
                        onclick="showDetails({{ json_encode($error) }})" 
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition text-sm">
                        <i class="fas fa-eye mr-2"></i>Ver Detalhes
                    </button>
                </div>

                <!-- Dados da Linha -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Dados da Linha:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        @foreach($error['data'] as $key => $value)
                            <div>
                                <span class="text-gray-600 font-medium">{{ $key }}:</span>
                                <span class="text-gray-900 ml-2">{{ $value ?: '(vazio)' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Erros -->
                <div class="bg-red-50 rounded-lg p-4">
                    <h4 class="font-semibold text-red-900 mb-2">
                        <i class="fas fa-times-circle mr-1"></i>Erros:
                    </h4>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($error['errors'] as $errorMsg)
                            <li>{{ $errorMsg }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(count($errors) === 0)
    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
        <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
        <p class="text-gray-500 text-lg">Nenhum erro encontrado nesta importação!</p>
    </div>
    @endif
</div>

<!-- Modal para Detalhes -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-2 sm:p-4 md:p-6">
    <div class="bg-white rounded-lg shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] md:max-h-[90vh] max-w-full sm:max-w-2xl md:max-w-3xl lg:max-w-4xl flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="flex-shrink-0 bg-red-600 text-white p-3 sm:p-4 flex justify-between items-center">
            <h3 class="text-base sm:text-lg md:text-xl font-bold">
                <i class="fas fa-exclamation-triangle mr-2"></i>Detalhes do Erro
            </h3>
            <button onclick="closeModal()" class="text-white hover:text-gray-200 text-xl sm:text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Content -->
        <div id="modalContent" class="flex-1 overflow-y-auto p-4 sm:p-6">
            <!-- Conteúdo será inserido via JavaScript -->
        </div>
    </div>
</div>

<script>
// Pesquisa em tempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const errorItems = document.querySelectorAll('.error-item');
    let visibleCount = 0;

    errorItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.classList.remove('hidden');
            visibleCount++;
        } else {
            item.classList.add('hidden');
        }
    });

    document.getElementById('errorCount').textContent = visibleCount;
});

function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.querySelectorAll('.error-item').forEach(item => {
        item.classList.remove('hidden');
    });
    document.getElementById('errorCount').textContent = {{ count($errors) }};
}

function showDetails(error) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    
    let html = '<div class="space-y-4 sm:space-y-6">';
    
    // Linha
    html += `<div class="flex-shrink-0">
        <h4 class="font-bold text-gray-900 text-base sm:text-lg mb-2">
            <i class="fas fa-file-alt text-red-600 mr-2"></i>Linha ${error.line}
        </h4>
    </div>`;
    
    // Dados
    html += '<div class="flex-shrink-0"><h4 class="font-bold text-gray-900 text-base sm:text-lg mb-3"><i class="fas fa-database text-blue-600 mr-2"></i>Dados da Linha</h4>';
    html += '<div class="bg-gray-50 rounded-lg p-3 sm:p-4"><div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">';
    
    for (const [key, value] of Object.entries(error.data)) {
        html += `<div class="break-words">
            <span class="text-gray-600 font-medium">${key}:</span>
            <span class="text-gray-900 ml-2">${value || '(vazio)'}</span>
        </div>`;
    }
    
    html += '</div></div></div>';
    
    // Erros
    html += '<div class="flex-shrink-0"><h4 class="font-bold text-red-900 text-base sm:text-lg mb-3"><i class="fas fa-times-circle text-red-600 mr-2"></i>Mensagens de Erro</h4>';
    html += '<div class="bg-red-50 rounded-lg p-3 sm:p-4"><ul class="list-disc list-inside text-xs sm:text-sm text-red-700 space-y-2">';
    
    error.errors.forEach(err => {
        html += `<li class="break-words">${err}</li>`;
    });
    
    html += '</ul></div></div>';
    html += '</div>';
    
    content.innerHTML = html;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('detailsModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Fechar modal ao clicar fora
document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection
