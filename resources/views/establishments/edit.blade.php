@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('establishments.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-building text-blue-600 mr-3"></i>Editar Estabelecimento
            </h1>
            <p class="text-gray-600 mt-2">{{ $establishment->corporate_name }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('establishments.update', $establishment) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Informações Gerais -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    Informações Gerais
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Razão Social *</label>
                        <input 
                            type="text" 
                            name="corporate_name" 
                            value="{{ old('corporate_name', $establishment->corporate_name) }}"
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('corporate_name') border-red-500 @enderror"
                            placeholder="Nome completo da empresa"
                        >
                        @error('corporate_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nome Fantasia</label>
                        <input 
                            type="text" 
                            name="trade_name" 
                            value="{{ old('trade_name', $establishment->trade_name) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('trade_name') border-red-500 @enderror"
                            placeholder="Nome comercial da empresa"
                        >
                        @error('trade_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">CNPJ *</label>
                        <input 
                            type="text" 
                            name="cnpj" 
                            value="{{ old('cnpj', $establishment->cnpj) }}"
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('cnpj') border-red-500 @enderror"
                            placeholder="00.000.000/0000-00"
                        >
                        @error('cnpj')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Inscrição Estadual</label>
                        <input 
                            type="text" 
                            name="state_registration" 
                            value="{{ old('state_registration', $establishment->state_registration) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('state_registration') border-red-500 @enderror"
                            placeholder="Inscrição estadual"
                        >
                        @error('state_registration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="mb-8 pb-8 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                    Endereço
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Logradouro</label>
                        <input 
                            type="text" 
                            name="street" 
                            value="{{ old('street', $establishment->street) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('street') border-red-500 @enderror"
                            placeholder="Rua, Avenida, etc"
                        >
                        @error('street')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                        <input 
                            type="text" 
                            name="number" 
                            value="{{ old('number', $establishment->number) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('number') border-red-500 @enderror"
                            placeholder="123"
                        >
                        @error('number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Complemento</label>
                        <input 
                            type="text" 
                            name="complement" 
                            value="{{ old('complement', $establishment->complement) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('complement') border-red-500 @enderror"
                            placeholder="Sala, Andar, etc"
                        >
                        @error('complement')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Bairro</label>
                        <input 
                            type="text" 
                            name="neighborhood" 
                            value="{{ old('neighborhood', $establishment->neighborhood) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('neighborhood') border-red-500 @enderror"
                            placeholder="Nome do bairro"
                        >
                        @error('neighborhood')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cidade</label>
                        <input 
                            type="text" 
                            name="city" 
                            value="{{ old('city', $establishment->city) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('city') border-red-500 @enderror"
                            placeholder="Nome da cidade"
                        >
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">UF</label>
                        <input 
                            type="text" 
                            name="state" 
                            value="{{ old('state', $establishment->state) }}"
                            maxlength="2" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition uppercase @error('state') border-red-500 @enderror"
                            placeholder="SP"
                        >
                        @error('state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">CEP</label>
                        <input 
                            type="text" 
                            name="zip_code" 
                            value="{{ old('zip_code', $establishment->zip_code) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('zip_code') border-red-500 @enderror"
                            placeholder="00000-000"
                        >
                        @error('zip_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contato -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-phone text-blue-600 mr-2"></i>
                    Contato
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Telefone</label>
                        <input 
                            type="text" 
                            name="phone" 
                            value="{{ old('phone', $establishment->phone) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('phone') border-red-500 @enderror"
                            placeholder="(00) 0000-0000"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
                        <input 
                            type="email" 
                            name="email" 
                            value="{{ old('email', $establishment->email) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                            placeholder="contato@empresa.com"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('establishments.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i>Atualizar Estabelecimento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
