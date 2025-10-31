@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-plus text-blue-600 mr-3"></i>Novo Colaborador
            </h1>
            <p class="text-gray-600 mt-2">Cadastre um novo colaborador no sistema</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('employees.store') }}" method="POST">
            @csrf

            <!-- Informações Pessoais -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    Informações Pessoais
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nome Completo *</label>
                        <input 
                            type="text" 
                            name="full_name" 
                            value="{{ old('full_name') }}"
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('full_name') border-red-500 @enderror"
                            placeholder="Nome completo do colaborador"
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('cpf') border-red-500 @enderror"
                            placeholder="000.000.000-00"
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('pis_pasep') border-red-500 @enderror"
                            placeholder="000.00000.00-0"
                        >
                        @error('pis_pasep')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Matrícula</label>
                        <input 
                            type="text" 
                            name="matricula" 
                            value="{{ old('matricula') }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('matricula') border-red-500 @enderror"
                            placeholder="Ex: 1234"
                        >
                        @error('matricula')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">CTPS</label>
                        <input 
                            type="text" 
                            name="ctps" 
                            value="{{ old('ctps') }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('ctps') border-red-500 @enderror"
                            placeholder="Carteira de trabalho"
                        >
                        @error('ctps')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Informações de Trabalho -->
            <div class="mb-8 pb-8 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-briefcase text-blue-600 mr-2"></i>
                    Informações de Trabalho
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Data de Admissão *</label>
                        <input 
                            type="date" 
                            name="admission_date" 
                            value="{{ old('admission_date') }}"
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('admission_date') border-red-500 @enderror"
                        >
                        @error('admission_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Função</label>
                        <input 
                            type="text" 
                            name="position" 
                            value="{{ old('position') }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('position') border-red-500 @enderror"
                            placeholder="Cargo do colaborador"
                        >
                        @error('position')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Estabelecimento *</label>
                        <select 
                            name="establishment_id" 
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('establishment_id') border-red-500 @enderror"
                        >
                            <option value="">Selecione...</option>
                            @foreach($establishments as $est)
                                <option value="{{ $est->id }}" {{ old('establishment_id') == $est->id ? 'selected' : '' }}>
                                    {{ $est->corporate_name }}
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('department_id') border-red-500 @enderror"
                        >
                            <option value="">Nenhum</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                        <select 
                            name="status" 
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('status') border-red-500 @enderror"
                        >
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativo</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                            <option value="on_leave" {{ old('status') == 'on_leave' ? 'selected' : '' }}>Afastado</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('employees.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i>Salvar Colaborador
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
