@extends('layouts.main')

@section('title', 'Novo Administrador')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-4 mb-4">
            <a href="{{ route('admins.index') }}" class="text-gray-600 hover:text-gray-900 transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Novo Administrador</h1>
                <p class="text-gray-600 mt-1">Cadastre um novo usuário com acesso administrativo</p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-lg p-8">
        <form method="POST" action="{{ route('admins.store') }}">
            @csrf

            <!-- Nome Completo -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('name') border-red-500 @enderror"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- CPF -->
            <div class="mb-6">
                <label for="cpf" class="block text-sm font-semibold text-gray-700 mb-2">
                    CPF <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="cpf" 
                    id="cpf" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('cpf') border-red-500 @enderror"
                    value="{{ old('cpf') }}"
                    maxlength="14"
                    placeholder="000.000.000-00"
                    required
                >
                @error('cpf')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estabelecimento -->
            <div class="mb-6">
                <label for="establishment_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    Estabelecimento (Opcional)
                </label>
                <select 
                    name="establishment_id" 
                    id="establishment_id" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('establishment_id') border-red-500 @enderror"
                >
                    <option value="">Todos os estabelecimentos</option>
                    @foreach($establishments as $establishment)
                        <option value="{{ $establishment->id }}" {{ old('establishment_id') == $establishment->id ? 'selected' : '' }}>
                            {{ $establishment->corporate_name }}
                        </option>
                    @endforeach
                </select>
                @error('establishment_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Se não selecionar, o administrador terá acesso a todos os estabelecimentos
                </p>
            </div>

            <!-- Senha -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    Senha <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('password') border-red-500 @enderror"
                    required
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">Mínimo de 6 caracteres</p>
            </div>

            <!-- Confirmar Senha -->
            <div class="mb-8">
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                    Confirmar Senha <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    required
                >
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admins.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl"
                >
                    <i class="fas fa-save mr-2"></i>Cadastrar Administrador
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Máscara de CPF
    document.getElementById('cpf').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        }
    });
</script>
@endsection
