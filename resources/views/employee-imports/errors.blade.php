@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employee-imports.show', $import) }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>Erros da Importação #{{ $import->id }}
            </h1>
            <p class="text-gray-600 mt-2">{{ $import->original_filename }} - {{ count($errorRows) }} {{ count($errorRows) === 1 ? 'linha com erro' : 'linhas com erros' }}</p>
        </div>
    </div>

    <!-- Summary Banner -->
    <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-6 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-red-600 text-2xl mr-4 mt-1"></i>
            <div class="flex-1">
                <h3 class="font-bold text-red-900 mb-2">Sobre esta Página</h3>
                <p class="text-sm text-red-800 mb-3">
                    Esta página mostra todas as linhas do arquivo CSV que apresentaram erros durante a importação.
                    Para cada linha, você pode ver os dados originais e os erros específicos encontrados.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="bg-white rounded-lg p-3 border border-red-200">
                        <p class="text-xs text-red-700 font-medium mb-1">Total de Erros</p>
                        <p class="text-2xl font-bold text-red-900">{{ count($errorRows) }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-red-200">
                        <p class="text-xs text-red-700 font-medium mb-1">Taxa de Erro</p>
                        <p class="text-2xl font-bold text-red-900">{{ number_format(($import->error_count / $import->total_rows) * 100, 1) }}%</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-red-200">
                        <p class="text-xs text-red-700 font-medium mb-1">Total de Linhas</p>
                        <p class="text-2xl font-bold text-red-900">{{ $import->total_rows }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>Pesquisar
                </label>
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Pesquisar por CPF, Nome, Matrícula..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter mr-1"></i>Filtrar por Tipo
                </label>
                <select 
                    id="errorTypeFilter" 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Todos os Erros</option>
                    <option value="cpf">CPF</option>
                    <option value="pis">PIS/PASEP</option>
                    <option value="matricula">Matrícula</option>
                    <option value="establishment">Estabelecimento</option>
                    <option value="department">Departamento</option>
                    <option value="admission_date">Data Admissão</option>
                    <option value="full_name">Nome</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Errors Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" id="errorsTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700 w-20">Linha</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">CPF</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Nome Completo</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Matrícula</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Estabelec.</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700 w-64">Erros</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700 w-24">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($errorRows as $errorRow)
                    <tr class="hover:bg-gray-50 transition error-row" 
                        data-search="{{ strtolower(json_encode($errorRow['data'])) }}"
                        data-errors="{{ strtolower(implode(' ', $errorRow['errors'])) }}">
                        <td class="px-4 py-4">
                            <div class="bg-red-600 text-white font-bold px-3 py-1 rounded text-sm inline-block">
                                {{ $errorRow['line'] }}
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="font-mono text-sm text-gray-900">{{ $errorRow['data']['cpf'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-gray-900">{{ $errorRow['data']['full_name'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="font-mono text-sm text-gray-900">{{ $errorRow['data']['matricula'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm text-gray-600">ID: {{ $errorRow['data']['establishment_id'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                @foreach($errorRow['errors'] as $error)
                                <div class="flex items-start text-xs text-red-700">
                                    <i class="fas fa-times-circle mr-1 mt-0.5 flex-shrink-0"></i>
                                    <span>{{ $error }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <button 
                                onclick="showDetails({{ json_encode($errorRow) }})" 
                                class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded transition"
                                title="Ver todos os dados">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Results Counter -->
    <div class="mt-4 text-center">
        <p class="text-sm text-gray-600">
            Mostrando <span id="visibleCount">{{ count($errorRows) }}</span> de {{ count($errorRows) }} linhas com erro
        </p>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between pt-6">
        <a href="{{ route('employee-imports.show', $import) }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left mr-2"></i>Voltar para Detalhes
        </a>
        <a href="{{ route('employee-imports.template') }}" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition shadow-lg">
            <i class="fas fa-download mr-2"></i>Baixar Modelo CSV
        </a>
    </div>
</div>

<!-- Modal for Full Details -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-2 sm:p-4 md:p-6">
    <div class="bg-white rounded-lg shadow-xl w-full h-full sm:h-auto sm:max-h-[95vh] md:max-h-[90vh] max-w-full sm:max-w-2xl md:max-w-3xl lg:max-w-4xl flex flex-col overflow-hidden">
        <!-- Header Fixo -->
        <div class="flex-shrink-0 bg-white border-b border-gray-200 px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between">
            <h3 class="text-base sm:text-lg md:text-xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-2 text-sm sm:text-base"></i>
                <span class="truncate">Detalhes da Linha</span>
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition flex-shrink-0 ml-2">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>
        
        <!-- Conteúdo com Scroll -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6" id="modalContent"></div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('errorTypeFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filterType = document.getElementById('errorTypeFilter').value.toLowerCase();
    const rows = document.querySelectorAll('.error-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const searchData = row.getAttribute('data-search');
        const errorData = row.getAttribute('data-errors');
        
        let matchesSearch = searchTerm === '' || searchData.includes(searchTerm);
        let matchesFilter = filterType === '' || errorData.includes(filterType);

        if (matchesSearch && matchesFilter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('visibleCount').textContent = visibleCount;
}

// Show details modal
function showDetails(errorRow) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    
    let html = `
        <div class="space-y-4 sm:space-y-6">
            <!-- Seção de Erros -->
            <div class="bg-red-50 border-l-4 border-red-600 p-3 sm:p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-600 text-base sm:text-xl mr-2 sm:mr-3 mt-0.5 sm:mt-1 flex-shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-red-900 mb-2 text-sm sm:text-base">Linha ${errorRow.line}</h4>
                        <div class="space-y-1">
                            ${errorRow.errors.map(err => `
                                <div class="flex items-start text-xs sm:text-sm text-red-800">
                                    <i class="fas fa-times-circle mr-1.5 sm:mr-2 mt-0.5 flex-shrink-0"></i>
                                    <span class="break-words">${err}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Dados -->
            <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                <h4 class="font-bold text-gray-900 mb-3 sm:mb-4 flex items-center text-sm sm:text-base">
                    <i class="fas fa-database text-gray-600 mr-2 text-sm sm:text-base"></i>
                    Dados da Linha
                </h4>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 md:gap-4">
                    ${Object.entries(errorRow.data).map(([key, value]) => `
                        <div class="bg-white p-2 sm:p-3 rounded border border-gray-200">
                            <dt class="text-xs font-medium text-gray-500 uppercase mb-1 truncate" title="${key}">${key}</dt>
                            <dd class="text-xs sm:text-sm font-mono text-gray-900 break-all">${value || '<span class="text-gray-400">vazio</span>'}</dd>
                        </div>
                    `).join('')}
                </dl>
            </div>

            <!-- Seção de Dicas -->
            <div class="bg-blue-50 border-l-4 border-blue-600 p-3 sm:p-4 rounded">
                <h4 class="font-bold text-blue-900 mb-2 flex items-center text-sm sm:text-base">
                    <i class="fas fa-lightbulb mr-2 text-sm sm:text-base"></i>Como Corrigir
                </h4>
                <ul class="text-xs sm:text-sm text-blue-800 space-y-1.5 sm:space-y-2">
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Verifique cada campo listado nos erros acima</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Corrija os valores no arquivo CSV original</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Reimporte apenas as linhas corrigidas</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Consulte o GUIA_ERROS_IMPORTACAO.md para detalhes</span>
                    </li>
                </ul>
            </div>
        </div>
    `;
    
    content.innerHTML = html;
    modal.classList.remove('hidden');
    // Prevenir scroll do body quando modal está aberto
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('detailsModal').classList.add('hidden');
    // Restaurar scroll do body
    document.body.style.overflow = '';
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Close modal on background click
document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
