@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="mb-6">
        <a href="{{ route('timesheets.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>Voltar para busca
        </a>
        
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-calendar-check text-blue-600 mr-3"></i>Selecionar Vínculos
        </h1>
        <p class="text-gray-600 mt-2">Escolha os vínculos (matrículas) para gerar os cartões de ponto</p>
    </div>

    <div class="max-w-6xl mx-auto w-full">
        <!-- Dados da Pessoa -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-user text-blue-600 mr-2"></i>
                Dados Pessoais
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-600">Nome Completo:</label>
                    <p class="text-gray-900">{{ $person->full_name }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">CPF:</label>
                    <p class="text-gray-900">{{ $person->cpf_formatted }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">PIS/PASEP:</label>
                    <p class="text-gray-900">{{ $person->pis_pasep_formatted ?? 'Não informado' }}</p>
                </div>
            </div>
        </div>

        <!-- Formulário de Seleção de Vínculos -->
        <form action="{{ route('timesheets.generate-multiple') }}" method="POST">
            @csrf
            <input type="hidden" name="person_id" value="{{ $person->id }}">

            <!-- Período -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar text-purple-600 mr-2"></i>
                    Período
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Data Inicial *
                        </label>
                        <input 
                            type="date" 
                            name="start_date" 
                            id="start_date"
                            value="{{ old('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Data Final *
                        </label>
                        <input 
                            type="date" 
                            name="end_date" 
                            id="end_date"
                            value="{{ old('end_date', now()->endOfMonth()->format('Y-m-d')) }}"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                </div>
            </div>

            <!-- Lista de Vínculos -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-id-card text-green-600 mr-2"></i>
                        Vínculos (Matrículas) - Selecione um ou mais
                    </h2>
                    <button 
                        type="button" 
                        onclick="toggleAllRegistrations()"
                        class="text-sm text-blue-600 hover:text-blue-800 font-semibold"
                    >
                        <i class="fas fa-check-double mr-1"></i>
                        Selecionar/Desmarcar Todos
                    </button>
                </div>

                @if($person->activeRegistrations->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
                        <p>Esta pessoa não possui vínculos ativos no momento.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($person->activeRegistrations as $registration)
                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-blue-50 transition registration-item {{ $loop->first ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                                <input 
                                    type="checkbox" 
                                    name="registration_ids[]" 
                                    value="{{ $registration->id }}"
                                    class="registration-checkbox mt-1 w-5 h-5 text-blue-600"
                                    {{ $loop->first ? 'checked' : '' }}
                                >
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-bold text-gray-900">
                                                Matrícula: {{ $registration->matricula }}
                                            </h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $registration->position ?? 'Cargo não informado' }}
                                            </p>
                                        </div>
                                        @if($registration->currentWorkShiftAssignment)
                                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $registration->currentWorkShiftAssignment->template->name }}
                                            </span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Sem jornada
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3 text-sm">
                                        <div>
                                            <span class="text-gray-600">Estabelecimento:</span>
                                            <p class="font-semibold text-gray-900">
                                                {{ $registration->establishment ? ($registration->establishment->name ?: $registration->establishment->corporate_name) : 'N/A' }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Departamento:</span>
                                            <p class="font-semibold text-gray-900">
                                                {{ $registration->department->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Admissão:</span>
                                            <p class="font-semibold text-gray-900">
                                                {{ $registration->admission_date->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Status:</span>
                                            <p class="font-semibold text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>Ativo
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end gap-3">
                <a 
                    href="{{ route('timesheets.index') }}" 
                    class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition"
                >
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
                <button 
                    type="submit" 
                    id="generateBtn"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition"
                    disabled
                >
                    <i class="fas fa-file-pdf mr-2"></i>
                    Gerar Cartões de Ponto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAllRegistrations() {
    const checkboxes = document.querySelectorAll('.registration-checkbox');
    const firstChecked = checkboxes[0].checked;
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !firstChecked;
        updateItemStyle(checkbox);
    });
    
    updateGenerateButton();
}

function updateItemStyle(checkbox) {
    const item = checkbox.closest('.registration-item');
    if (checkbox.checked) {
        item.classList.add('border-blue-500', 'bg-blue-50');
        item.classList.remove('border-gray-200');
    } else {
        item.classList.remove('border-blue-500', 'bg-blue-50');
        item.classList.add('border-gray-200');
    }
}

function updateGenerateButton() {
    const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
    const btn = document.getElementById('generateBtn');
    
    if (checkedBoxes.length > 0) {
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-file-pdf mr-2"></i>Gerar ${checkedBoxes.length} Cartão${checkedBoxes.length > 1 ? 'ões' : ''} de Ponto`;
    } else {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-lock mr-2"></i>Selecione pelo menos um vínculo';
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.registration-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateItemStyle(this);
            updateGenerateButton();
        });
    });
    
    // Atualiza estado inicial
    updateGenerateButton();
});
</script>
@endsection
