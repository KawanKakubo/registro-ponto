@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="mb-6">
        <a href="{{ route('employees.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>Voltar para lista
        </a>

        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-user text-blue-600 mr-3"></i>{{ $person->full_name }}
                </h1>
                <p class="text-gray-600 mt-2">Dados pessoais e vínculos empregatícios</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('employees.edit', $person) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded-lg transition">
                    <i class="fas fa-edit mr-2"></i>Editar Dados Pessoais
                </a>
                <a href="{{ route('registrations.create', $person) }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Adicionar Vínculo
                </a>
            </div>
        </div>
    </div>

    <!-- Dados Pessoais -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-id-card text-purple-600 mr-2"></i>Dados Pessoais
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-600">CPF:</label>
                <p class="text-gray-900">{{ $person->cpf_formatted }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">PIS/PASEP:</label>
                <p class="text-gray-900">{{ $person->pis_pasep_formatted ?? 'Não informado' }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">CTPS:</label>
                <p class="text-gray-900">{{ $person->ctps ?? 'Não informado' }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Cadastrado em:</label>
                <p class="text-gray-900">{{ $person->created_at?->format('d/m/Y H:i') ?? 'Não informado' }}</p>
            </div>
        </div>
    </div>

    <!-- Vínculos -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-briefcase text-green-600 mr-2"></i>Vínculos Empregatícios
            </h2>
            <span class="text-sm text-gray-600">
                Total: {{ $person->employeeRegistrations->count() }} vínculo(s)
            </span>
        </div>

        @if($person->employeeRegistrations->isEmpty())
            <div class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                <i class="fas fa-briefcase text-gray-400 text-4xl mb-3"></i>
                <p class="font-semibold">Nenhum vínculo cadastrado</p>
                <p class="text-sm mt-2">Clique em "Adicionar Vínculo" para criar o primeiro vínculo empregatício.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($person->employeeRegistrations as $registration)
                    <div class="border-2 rounded-lg p-4 {{ $registration->status === 'active' ? 'border-green-300 bg-green-50' : 'border-gray-300 bg-gray-50' }}">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-bold text-gray-900">
                                        Matrícula: {{ $registration->matricula }}
                                    </h3>
                                    @if($registration->status === 'active')
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                                            <i class="fas fa-check-circle mr-1"></i>Ativo
                                        </span>
                                    @elseif($registration->status === 'inactive')
                                        <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-3 py-1 rounded-full">
                                            <i class="fas fa-times-circle mr-1"></i>Inativo
                                        </span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full">
                                            <i class="fas fa-exclamation-circle mr-1"></i>Afastado
                                        </span>
                                    @endif
                                    
                                    @if($registration->currentWorkShiftAssignment)
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                            <i class="fas fa-clock mr-1"></i>{{ $registration->currentWorkShiftAssignment->template->name }}
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Sem jornada
                                        </span>
                                    @endif
                                </div>
                                <p class="text-gray-600 mt-1">{{ $registration->position ?? 'Cargo não informado' }}</p>
                            </div>
                            
                            <div class="flex gap-2">
                                <a href="{{ route('registrations.edit', $registration) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm transition">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>
                                
                                @if($registration->status === 'active')
                                    <form action="{{ route('registrations.terminate', $registration) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Tem certeza que deseja encerrar este vínculo?')" class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded text-sm transition">
                                            <i class="fas fa-stop-circle mr-1"></i>Encerrar
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('registrations.reactivate', $registration) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition">
                                            <i class="fas fa-play-circle mr-1"></i>Reativar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600 font-semibold">Estabelecimento:</span>
                                <p class="text-gray-900">{{ $registration->establishment->corporate_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600 font-semibold">Departamento:</span>
                                <p class="text-gray-900">{{ $registration->department->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600 font-semibold">Admissão:</span>
                                <p class="text-gray-900">{{ $registration->admission_date?->format('d/m/Y') ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600 font-semibold">Cadastrado em:</span>
                                <p class="text-gray-900">{{ $registration->created_at?->format('d/m/Y') ?? 'Não informado' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Ações de Exclusão -->
    <div class="mt-6 flex justify-end">
        <form action="{{ route('employees.destroy', $person) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Isso irá excluir a pessoa e TODOS os seus vínculos. Esta ação não pode ser desfeita. Deseja continuar?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                <i class="fas fa-trash mr-2"></i>Excluir Pessoa e Todos os Vínculos
            </button>
        </form>
    </div>
</div>
@endsection
