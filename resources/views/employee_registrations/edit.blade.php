@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employees.show', $registration->person) }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-edit text-yellow-600 mr-3"></i>Editar Vínculo
            </h1>
            <p class="text-gray-600 mt-2">{{ $registration->person->full_name }} - Matrícula: {{ $registration->matricula }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('registrations.update', $registration) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Matrícula *</label>
                    <input 
                        type="text" 
                        name="matricula" 
                        value="{{ old('matricula', $registration->matricula) }}"
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
                        value="{{ old('admission_date', $registration->admission_date->format('Y-m-d')) }}"
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
                            <option value="{{ $establishment->id }}" {{ old('establishment_id', $registration->establishment_id) == $establishment->id ? 'selected' : '' }}>
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
                            <option value="{{ $department->id }}" {{ old('department_id', $registration->department_id) == $department->id ? 'selected' : '' }}>
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
                        value="{{ old('position', $registration->position) }}"
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
                        <option value="active" {{ old('status', $registration->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ old('status', $registration->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                        <option value="on_leave" {{ old('status', $registration->status) == 'on_leave' ? 'selected' : '' }}>Afastado</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-between mt-8">
                <form action="{{ route('registrations.destroy', $registration) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este vínculo? Esta ação não pode ser desfeita.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                        <i class="fas fa-trash mr-2"></i>Excluir Vínculo
                    </button>
                </form>
                
                <div class="flex gap-4">
                    <a href="{{ route('employees.show', $registration->person) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i>Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
