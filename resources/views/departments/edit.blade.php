@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-sitemap text-blue-600 mr-3"></i>Editar Departamento
            </h1>
            <p class="text-gray-600 mt-2">{{ $department->name }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('departments.update', $department) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-building text-gray-400 mr-2"></i>Estabelecimento *
                    </label>
                    <select 
                        name="establishment_id" 
                        required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('establishment_id') border-red-500 @enderror"
                    >
                        <option value="">Selecione um estabelecimento...</option>
                        @foreach($establishments as $est)
                            <option value="{{ $est->id }}" {{ old('establishment_id', $department->establishment_id) == $est->id ? 'selected' : '' }}>
                                {{ $est->corporate_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('establishment_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-tag text-gray-400 mr-2"></i>Nome do Departamento *
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $department->name) }}"
                        required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('name') border-red-500 @enderror"
                        placeholder="Ex: Recursos Humanos, TI, Financeiro..."
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user-tie text-gray-400 mr-2"></i>Responsável
                    </label>
                    <input 
                        type="text" 
                        name="responsible" 
                        value="{{ old('responsible', $department->responsible) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('responsible') border-red-500 @enderror"
                        placeholder="Nome do responsável pelo departamento"
                    >
                    @error('responsible')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Info Stats -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-users text-blue-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Colaboradores neste departamento</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $department->employees()->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('departments.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i>Atualizar Departamento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
