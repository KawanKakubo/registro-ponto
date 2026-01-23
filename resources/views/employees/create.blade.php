@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-plus text-blue-600 mr-3"></i>Nova Pessoa
            </h1>
            <p class="text-gray-600 mt-2">Cadastre uma nova pessoa no sistema</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('employees.store') }}" method="POST">
            @csrf

            <!-- Dados Pessoais -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-id-card text-purple-600 mr-2"></i>
                    Dados Pessoais
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nome Completo *</label>
                        <input 
                            type="text" 
                            name="full_name" 
                            value="{{ old('full_name') }}"
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('full_name') border-red-500 @enderror"
                            placeholder="Nome completo da pessoa"
                        >
                        @error('full_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">CPF *</label>
                        <input 
                            type="text" 
                            name="cpf" 
                            value="{{ old('cpf') }}"
                            required 
                            maxlength="14"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cpf') border-red-500 @enderror"
                            placeholder="000.000.000-00"
                            id="cpf"
                        >
                        @error('cpf')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">PIS/PASEP</label>
                        <input 
                            type="text" 
                            name="pis_pasep" 
                            value="{{ old('pis_pasep') }}"
                            maxlength="15"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('pis_pasep') border-red-500 @enderror"
                            placeholder="000.00000.00-0"
                            id="pis_pasep"
                        >
                        @error('pis_pasep')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">CTPS</label>
                        <input 
                            type="text" 
                            name="ctps" 
                            value="{{ old('ctps') }}"
                            maxlength="20"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ctps') border-red-500 @enderror"
                            placeholder="Número da CTPS"
                        >
                        @error('ctps')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Opção: Criar Primeiro Vínculo -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <input 
                        type="checkbox" 
                        name="create_registration" 
                        id="create_registration" 
                        value="1"
                        {{ old('create_registration', true) ? 'checked' : '' }}
                        class="w-5 h-5 text-blue-600 mr-3"
                        onchange="toggleRegistrationFields()"
                    >
                    <label for="create_registration" class="text-lg font-bold text-gray-900">
                        <i class="fas fa-briefcase text-green-600 mr-2"></i>
                        Criar primeiro vínculo empregatício agora
                    </label>
                </div>

                <div id="registration_fields" class="{{ old('create_registration', true) ? '' : 'hidden' }}">
                    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6">
                        <p class="text-sm text-gray-600 mb-4">Preencha os dados do primeiro vínculo empregatício desta pessoa.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Matrícula *</label>
                                <input 
                                    type="text" 
                                    name="matricula" 
                                    value="{{ old('matricula') }}"
                                    maxlength="20"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('matricula') border-red-500 @enderror registration-field"
                                    placeholder="Número da matrícula"
                                >
                                @error('matricula')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Data de Admissão *</label>
                                <input 
                                    type="date" 
                                    name="admission_date" 
                                    value="{{ old('admission_date', now()->format('Y-m-d')) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('admission_date') border-red-500 @enderror registration-field"
                                >
                                @error('admission_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Estabelecimento *</label>
                                <select 
                                    name="establishment_id" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('establishment_id') border-red-500 @enderror registration-field"
                                >
                                    <option value="">Selecione...</option>
                                    @foreach($establishments as $establishment)
                                        <option value="{{ $establishment->id }}" {{ old('establishment_id') == $establishment->id ? 'selected' : '' }}>
                                            {{ $establishment->corporate_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('establishment_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Departamento</label>
                                <select 
                                    name="department_id" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_id') border-red-500 @enderror"
                                >
                                    <option value="">Selecione...</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Cargo/Função</label>
                                <input 
                                    type="text" 
                                    name="position" 
                                    value="{{ old('position') }}"
                                    maxlength="100"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('position') border-red-500 @enderror"
                                    placeholder="Ex: Analista de Sistemas, Motorista, etc."
                                >
                                @error('position')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('employees.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Salvar Pessoa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleRegistrationFields() {
    const checkbox = document.getElementById('create_registration');
    const fields = document.getElementById('registration_fields');
    const registrationInputs = document.querySelectorAll('.registration-field');
    
    if (checkbox.checked) {
        fields.classList.remove('hidden');
        registrationInputs.forEach(input => {
            if (input.name === 'matricula' || input.name === 'establishment_id' || input.name === 'admission_date') {
                input.required = true;
            }
        });
    } else {
        fields.classList.add('hidden');
        registrationInputs.forEach(input => input.required = false);
    }
}

// Máscaras
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    }
});

document.getElementById('pis_pasep').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{5})(\d)/, '$1.$2');
        value = value.replace(/(\d{2})(\d{1})$/, '$1-$2');
        e.target.value = value;
    }
});
</script>
@endsection
