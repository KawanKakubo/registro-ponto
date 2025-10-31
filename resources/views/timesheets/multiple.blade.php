@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-calendar-check text-blue-600 mr-3"></i>Cartões de Ponto Gerados
        </h1>
        <p class="text-gray-600 mt-2">
            {{ $employees->count() }} relatório(s) prontos para download 
            <span class="text-gray-400">•</span> 
            Período: {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
        </p>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Card: Total de Colaboradores -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Total de Colaboradores</p>
                    <h3 class="text-4xl font-bold">{{ $employees->count() }}</h3>
                </div>
                <div class="bg-white/20 rounded-full p-4">
                    <i class="fas fa-users text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Card: Período -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Período Selecionado</p>
                    <h3 class="text-2xl font-bold">{{ \Carbon\Carbon::parse($start_date)->diffInDays(\Carbon\Carbon::parse($end_date)) + 1 }} dias</h3>
                </div>
                <div class="bg-white/20 rounded-full p-4">
                    <i class="fas fa-calendar-alt text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Card: Departamento -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Departamento</p>
                    <h3 class="text-xl font-bold truncate">{{ $employees->first()->department->name ?? 'N/A' }}</h3>
                </div>
                <div class="bg-white/20 rounded-full p-4">
                    <i class="fas fa-building text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações em Massa -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center">
                <i class="fas fa-download text-blue-600 text-2xl mr-4"></i>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Download em Lote</h2>
                    <p class="text-gray-600 text-sm">Baixe todos os cartões de ponto de uma vez</p>
                </div>
            </div>
            <div class="flex gap-3">
                <form id="downloadZipForm" action="{{ route('timesheets.download-zip') }}" method="POST">
                    @csrf
                    @foreach($employees as $employee)
                        <input type="hidden" name="employee_ids[]" value="{{ $employee->id }}">
                    @endforeach
                    <input type="hidden" name="start_date" value="{{ $start_date }}">
                    <input type="hidden" name="end_date" value="{{ $end_date }}">
                    
                    <button 
                        type="submit"
                        id="downloadZipBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition inline-flex items-center"
                    >
                        <i class="fas fa-file-archive mr-2"></i>Baixar Todos (ZIP)
                    </button>
                </form>
                <a 
                    href="{{ route('timesheets.index') }}" 
                    class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition"
                >
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Lista de Colaboradores -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-list text-blue-600 mr-2"></i>
                Lista de Colaboradores
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">#</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            <i class="fas fa-user mr-2 text-gray-400"></i>Nome Completo
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            <i class="fas fa-id-card mr-2 text-gray-400"></i>CPF
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            <i class="fas fa-hashtag mr-2 text-gray-400"></i>Matrícula
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            <i class="fas fa-building mr-2 text-gray-400"></i>Departamento
                        </th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                            <i class="fas fa-download mr-2 text-gray-400"></i>Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($employees as $index => $employee)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 text-blue-600 rounded-full w-10 h-10 flex items-center justify-center font-bold mr-3">
                                        {{ substr($employee->full_name, 0, 1) }}
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $employee->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                {{ $employee->cpf_formatted }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $employee->matricula ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $employee->department->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a 
                                    href="{{ route('timesheets.show', ['employee_id' => $employee->id, 'start_date' => $start_date, 'end_date' => $end_date]) }}" 
                                    target="_blank"
                                    class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow hover:shadow-md"
                                >
                                    <i class="fas fa-file-pdf mr-2"></i>Visualizar PDF
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
            <div>
                <h3 class="font-bold text-blue-900 mb-2">Instruções</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li><i class="fas fa-check mr-2"></i>Clique em <strong>"Visualizar PDF"</strong> para ver o cartão de ponto individual no navegador</li>
                    <li><i class="fas fa-check mr-2"></i>Use <strong>"Baixar Todos (ZIP)"</strong> para baixar todos os PDFs compactados em um único arquivo</li>
                    <li><i class="fas fa-check mr-2"></i>O arquivo ZIP conterá um PDF para cada colaborador com nome formatado</li>
                    <li><i class="fas fa-check mr-2"></i>Use <strong>Ctrl+P</strong> no navegador para imprimir cada relatório individual</li>
                    <li><i class="fas fa-clock mr-2"></i>A geração do ZIP pode levar alguns segundos dependendo do número de colaboradores</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const downloadForm = document.getElementById('downloadZipForm');
    const downloadBtn = document.getElementById('downloadZipBtn');
    
    if (downloadForm && downloadBtn) {
        downloadForm.addEventListener('submit', function(e) {
            // Desabilita o botão temporariamente
            downloadBtn.disabled = true;
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando PDFs...';
            
            // Reabilita após 5 segundos (tempo estimado de processamento)
            setTimeout(function() {
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = '<i class="fas fa-file-archive mr-2"></i>Baixar Todos (ZIP)';
            }, 5000);
        });
    }
});
</script>

@endsection
