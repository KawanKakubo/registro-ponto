@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Saudação -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Bem-vindo(a) de volta, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-600 mt-2">Aqui está um resumo das atividades do sistema</p>
    </div>

    <!-- Cartões de Estatísticas Principais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Pessoas Cadastradas -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Pessoas Cadastradas</p>
                    <p class="text-4xl font-bold">{{ number_format($stats['total_people'], 0, ',', '.') }}</p>
                    <p class="text-blue-100 text-xs mt-1">{{ number_format($stats['active_registrations'], 0, ',', '.') }} vínculos ativos</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-users text-3xl text-blue-900"></i>
                </div>
            </div>
            <a href="{{ route('employees.index') }}" class="text-blue-100 hover:text-white text-sm flex items-center">
                Ver todos <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <!-- Vínculos Ativos -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Vínculos Ativos</p>
                    <p class="text-4xl font-bold">{{ number_format($stats['active_registrations'], 0, ',', '.') }}</p>
                    <p class="text-green-100 text-xs mt-1">
                        {{ number_format($stats['registrations_with_workshift'], 0, ',', '.') }} com jornada
                    </p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-id-card text-3xl text-green-900"></i>
                </div>
            </div>
            <span class="text-green-100 text-sm">{{ number_format($stats['inactive_registrations'], 0, ',', '.') }} inativos</span>
        </div>

        <!-- Estabelecimentos -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Estabelecimentos</p>
                    <p class="text-4xl font-bold">{{ number_format($stats['total_establishments'], 0, ',', '.') }}</p>
                    <p class="text-purple-100 text-xs mt-1">{{ number_format($stats['establishments_with_registrations'], 0, ',', '.') }} com vínculos</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-building text-3xl text-purple-900"></i>
                </div>
            </div>
            <a href="{{ route('establishments.index') }}" class="text-purple-100 hover:text-white text-sm flex items-center">
                Ver todos <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <!-- Marcações Hoje -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Marcações Hoje</p>
                    <p class="text-4xl font-bold">{{ number_format($stats['today_records'], 0, ',', '.') }}</p>
                    <p class="text-orange-100 text-xs mt-1">{{ number_format($stats['this_month_records'], 0, ',', '.') }} este mês</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-clock text-3xl text-orange-900"></i>
                </div>
            </div>
            <span class="text-orange-100 text-sm">{{ today()->format('d/m/Y') }}</span>
        </div>
    </div>

    <!-- Alertas -->
    @if($alerts['people_without_registrations']['count'] > 0 || $alerts['registrations_without_workshift']['count'] > 0)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-6 mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mr-3"></i>
            <h2 class="text-xl font-bold text-gray-900">Alertas e Notificações</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($alerts['people_without_registrations']['count'] > 0)
            <div class="bg-white rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-900">
                        <i class="fas fa-user-slash text-yellow-600 mr-2"></i>
                        Pessoas sem vínculos ativos
                    </h3>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">
                        {{ $alerts['people_without_registrations']['count'] }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">Pessoas cadastradas sem nenhum vínculo ativo</p>
                <a href="{{ route('employees.index') }}?filter=without_registrations" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Ver lista completa →
                </a>
            </div>
            @endif

            @if($alerts['registrations_without_workshift']['count'] > 0)
            <div class="bg-white rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-900">
                        <i class="fas fa-calendar-times text-yellow-600 mr-2"></i>
                        Vínculos sem jornada
                    </h3>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">
                        {{ $alerts['registrations_without_workshift']['count'] }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">Vínculos ativos sem jornada de trabalho atribuída</p>
                <a href="{{ route('work-shift-templates.bulk-assign') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Atribuir jornadas →
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Ações Rápidas -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Ações Rápidas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('afd-imports.create') }}" class="group bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg p-6 text-center transition duration-200 transform hover:scale-105">
                <div class="bg-blue-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                    <i class="fas fa-file-import text-2xl text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Importar Arquivo AFD</h3>
                <p class="text-sm text-gray-600">Envie arquivos de ponto eletrônico</p>
            </a>

            <a href="{{ route('timesheets.index') }}" class="group bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-6 text-center transition duration-200 transform hover:scale-105">
                <div class="bg-green-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                    <i class="fas fa-file-alt text-2xl text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Gerar Cartão de Ponto</h3>
                <p class="text-sm text-gray-600">Criar cartões individuais ou em lote</p>
            </a>

            <a href="{{ route('employees.create') }}" class="group bg-purple-50 hover:bg-purple-100 border-2 border-purple-200 rounded-lg p-6 text-center transition duration-200 transform hover:scale-105">
                <div class="bg-purple-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                    <i class="fas fa-user-plus text-2xl text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Adicionar Pessoa</h3>
                <p class="text-sm text-gray-600">Cadastre nova pessoa no sistema</p>
            </a>

            <a href="{{ route('work-shift-templates.bulk-assign') }}" class="group bg-orange-50 hover:bg-orange-100 border-2 border-orange-200 rounded-lg p-6 text-center transition duration-200 transform hover:scale-105">
                <div class="bg-orange-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                    <i class="fas fa-calendar-alt text-2xl text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Atribuir Jornadas</h3>
                <p class="text-sm text-gray-600">Atribuir jornadas em massa</p>
            </a>
        </div>
    </div>

    <!-- Gráficos - Linha 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Vínculos por Estabelecimento -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                Vínculos por Estabelecimento
            </h2>
            <div class="relative" style="height: 300px; width: 100%;">
                <canvas id="registrationsByEstablishmentChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Distribuição de Jornadas -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-chart-pie text-green-600 mr-2"></i>
                Distribuição de Jornadas
            </h2>
            <div class="relative" style="height: 300px; width: 100%;">
                <canvas id="workshiftDistributionChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráficos - Linha 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Timeline de Importações (últimos 30 dias) -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-chart-line text-purple-600 mr-2"></i>
                Importações AFD (30 dias)
            </h2>
            <div class="relative" style="height: 300px; width: 100%;">
                <canvas id="importsTimelineChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Vínculos por Status -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-chart-donut text-orange-600 mr-2"></i>
                Vínculos por Status
            </h2>
            <div class="relative" style="height: 300px; width: 100%;">
                <canvas id="registrationsByStatusChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Atividade Recente -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Atividade Recente</h2>
            <a href="{{ route('afd-imports.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Ver todas <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        @if($recentActivity['imports']->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Arquivo</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Modelo do Relógio</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Usuário</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Data/Hora</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentActivity['imports'] as $import)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-4 px-4">
                            <div class="flex items-center">
                                <i class="fas fa-file text-gray-400 mr-3"></i>
                                <span class="font-medium text-gray-900">{{ Str::limit($import->file_name, 40) }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-gray-600">
                            {{ $import->format_type ?? 'N/A' }}
                        </td>
                        <td class="py-4 px-4 text-gray-600">
                            {{ $import->user ? $import->user->name : 'Sistema' }}
                        </td>
                        <td class="py-4 px-4 text-gray-600">
                            {{ $import->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($import->status === 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Concluído
                                </span>
                            @elseif($import->status === 'processing')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Em Processamento
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
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500 text-lg">Nenhuma importação realizada ainda</p>
            <a href="{{ route('afd-imports.create') }}" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                Fazer primeira importação <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Configuração global do Chart.js para prevenir loops infinitos
Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;
Chart.defaults.animation = {
    duration: 750,
    easing: 'easeInOutQuart'
};
Chart.defaults.interaction = {
    mode: 'nearest',
    axis: 'x',
    intersect: false
};

// Aguardar o DOM estar completamente carregado
document.addEventListener('DOMContentLoaded', function() {
    // Variável para armazenar instâncias dos gráficos
    const chartInstances = {};

    // Gráfico: Vínculos por Estabelecimento
    const ctxEstablishments = document.getElementById('registrationsByEstablishmentChart');
    if (ctxEstablishments) {
        // Destruir gráfico anterior se existir
        if (chartInstances.establishments) {
            chartInstances.establishments.destroy();
        }
        
        chartInstances.establishments = new Chart(ctxEstablishments, {
            type: 'bar',
            data: {
                labels: @json($charts['registrations_by_establishment']['labels']),
                datasets: [{
                    label: 'Vínculos Ativos',
                    data: @json($charts['registrations_by_establishment']['values']),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 200,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Gráfico: Distribuição de Jornadas
    const ctxWorkshifts = document.getElementById('workshiftDistributionChart');
    if (ctxWorkshifts) {
        // Destruir gráfico anterior se existir
        if (chartInstances.workshifts) {
            chartInstances.workshifts.destroy();
        }
        
        chartInstances.workshifts = new Chart(ctxWorkshifts, {
            type: 'pie',
            data: {
                labels: @json($charts['workshift_distribution']['labels']),
                datasets: [{
                    data: @json($charts['workshift_distribution']['values']),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(132, 204, 22, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 200,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
    }

    // Gráfico: Timeline de Importações AFD (últimos 30 dias)
    const ctxTimeline = document.getElementById('importsTimelineChart');
    if (ctxTimeline) {
        // Destruir gráfico anterior se existir
        if (chartInstances.timeline) {
            chartInstances.timeline.destroy();
        }
        
        chartInstances.timeline = new Chart(ctxTimeline, {
            type: 'line',
            data: {
                labels: @json($charts['imports_timeline']['labels']),
                datasets: [{
                    label: 'Importações',
                    data: @json($charts['imports_timeline']['values']),
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderColor: 'rgb(139, 92, 246)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 200,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Gráfico: Vínculos por Status (donut chart)
    const ctxStatus = document.getElementById('registrationsByStatusChart');
    if (ctxStatus) {
        // Destruir gráfico anterior se existir
        if (chartInstances.status) {
            chartInstances.status.destroy();
        }
        
        chartInstances.status = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: @json($charts['registrations_by_status']['labels']),
                datasets: [{
                    data: @json($charts['registrations_by_status']['values']),
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',  // Ativo - verde
                        'rgba(239, 68, 68, 0.8)',   // Inativo - vermelho
                        'rgba(251, 146, 60, 0.8)',  // Afastamento - laranja
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 200,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
    }

    // Prevenir loops infinitos no resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Os gráficos vão se redimensionar automaticamente
            // mas apenas depois de 200ms sem novos eventos de resize
        }, 200);
    });
});
</script>
@endsection
