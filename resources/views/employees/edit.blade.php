@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employees.show', $person) }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-edit text-yellow-600 mr-3"></i>Editar Dados Pessoais
            </h1>
            <p class="text-gray-600 mt-2">{{ $person->full_name }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('employees.update', $person) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nome Completo *</label>
                    <input 
                        type="text" 
                        name="full_name" 
                        value="{{ old('full_name', $person->full_name) }}"
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
                        value="{{ old('cpf', $person->cpf_formatted) }}"
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
                        value="{{ old('pis_pasep', $person->pis_pasep_formatted) }}"
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
                        value="{{ old('ctps', $person->ctps) }}"
                        maxlength="20"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ctps') border-red-500 @enderror"
                        placeholder="Número da CTPS"
                    >
                    @error('ctps')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4 mt-8">
                <a href="{{ route('employees.show', $person) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Máscara CPF
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    }
});

// Máscara PIS
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
