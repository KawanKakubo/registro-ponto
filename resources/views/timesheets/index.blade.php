@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-calendar-check text-blue-600 mr-3"></i>Cartão de Ponto
        </h1>
        <p class="text-gray-600 mt-2">Gere relatórios de ponto dos colaboradores</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Painel de Filtros (Esquerda) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-filter text-blue-600 mr-2"></i>
                    Filtros
                </h2>

                <form action="{{ route('timesheets.generate') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-gray-400 mr-2"></i>Colaborador *
                        </label>
                        <select 
                            name="employee_id" 
                            required 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('employee_id') border-red-500 @enderror"
                        >
                            <option value="">Selecione um colaborador</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-gray-400 mr-2"></i>Data Inicial *
                        </label>
                        <input 
                            type="date" 
                            name="start_date" 
                            value="{{ old('start_date') }}"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('start_date') border-red-500 @enderror"
                        >
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-gray-400 mr-2"></i>Data Final *
                        </label>
                        <input 
                            type="date" 
                            name="end_date" 
                            value="{{ old('end_date') }}"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('end_date') border-red-500 @enderror"
                        >
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition"
                    >
                        <i class="fas fa-file-pdf mr-2"></i>Gerar Cartão de Ponto
                    </button>
                </form>
            </div>

            <!-- Info Card -->
            <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4 mt-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="font-bold text-blue-900 mb-2">Como funciona?</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li><i class="fas fa-check mr-2"></i>Selecione o colaborador</li>
                            <li><i class="fas fa-check mr-2"></i>Defina o período desejado</li>
                            <li><i class="fas fa-check mr-2"></i>Gere o PDF do cartão de ponto</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Área de Visualização (Direita) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center py-12">
                    <i class="fas fa-file-invoice text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Nenhum relatório gerado</h3>
                    <p class="text-gray-600 mb-6">Preencha os filtros ao lado e clique em "Gerar Cartão de Ponto"</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8 text-left">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-clock text-blue-600 text-xl mr-3"></i>
                                <h4 class="font-bold text-gray-900">Registros Completos</h4>
                            </div>
                            <p class="text-sm text-gray-600">Todos os horários de entrada e saída do período selecionado</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-calculator text-green-600 text-xl mr-3"></i>
                                <h4 class="font-bold text-gray-900">Cálculos Automáticos</h4>
                            </div>
                            <p class="text-sm text-gray-600">Horas trabalhadas, extras e faltas calculadas automaticamente</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-file-pdf text-red-600 text-xl mr-3"></i>
                                <h4 class="font-bold text-gray-900">Formato PDF</h4>
                            </div>
                            <p class="text-sm text-gray-600">Relatório profissional pronto para impressão</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-stamp text-purple-600 text-xl mr-3"></i>
                                <h4 class="font-bold text-gray-900">Conforme Lei</h4>
                            </div>
                            <p class="text-sm text-gray-600">Segue a Portaria 671/2021 do MTP</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
