@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employees.show', $person) }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-plus-circle text-green-600 mr-3"></i>Novo Vínculo Empregatício
            </h1>
            <p class="text-gray-600 mt-2">{{ $person->full_name }} - {{ $person->cpf_formatted }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('registrations.store', $person) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Matrícula *</label>
                    <input 
                        type="text" 
                        name="matricula" 
                        value="{{ old('matricula') }}"
                        required 
                        maxlength="20"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('matricula') border-red-500 @enderror"
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
                        required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('admission_date') border-red-500 @enderror"
                    >
                    @error('admission_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estabelecimento *</label>
                    <select 
                        name="establishment_id" 
                        required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('establishment_id') border-red-500 @enderror"
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

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                    <select 
                        name="status" 
                        required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror"
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

            <!-- Botões -->
            <div class="flex justify-end gap-4 mt-8">
                <a href="{{ route('employees.show', $person) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Criar Vínculo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
