@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="mb-6">
        <a href="{{ route('timesheets.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>Voltar para busca
        </a>
        
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-users text-blue-600 mr-3"></i>Selecionar Pessoa
        </h1>
        <p class="text-gray-600 mt-2">Foram encontradas múltiplas pessoas. Selecione a pessoa desejada.</p>
    </div>

    <div class="max-w-4xl mx-auto w-full">
        <!-- Lista de Pessoas -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    {{ $people->count() }} pessoa(s) encontrada(s)
                </h2>
            </div>
            
            <div class="divide-y divide-gray-200">
                @foreach($people as $person)
                    <a href="{{ route('timesheets.person-registrations', $person) }}" 
                       class="block p-4 hover:bg-blue-50 transition-colors cursor-pointer">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">
                                            {{ $person->full_name }}
                                        </h3>
                                        <div class="flex items-center gap-4 text-sm text-gray-600 mt-1">
                                            @if($person->cpf)
                                                <span>
                                                    <i class="fas fa-id-card mr-1"></i>
                                                    CPF: {{ $person->cpf_formatted }}
                                                </span>
                                            @endif
                                            @if($person->pis_pasep)
                                                <span>
                                                    <i class="fas fa-file-alt mr-1"></i>
                                                    PIS: {{ $person->pis_pasep_formatted ?? $person->pis_pasep }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Vínculos ativos -->
                                @if($person->activeRegistrations && $person->activeRegistrations->count() > 0)
                                    <div class="mt-3 ml-14">
                                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">
                                            Vínculos Ativos ({{ $person->activeRegistrations->count() }})
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($person->activeRegistrations as $registration)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-briefcase mr-1"></i>
                                                    {{ $registration->matricula }}
                                                    @if($registration->department)
                                                        - {{ $registration->department->name }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-3 ml-14">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Sem vínculos ativos
                                        </span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="ml-4">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Dica -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                <div class="text-sm text-blue-700">
                    <p class="font-semibold mb-1">Dica:</p>
                    <p>Clique na pessoa desejada para ver os vínculos disponíveis e gerar o cartão de ponto.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
