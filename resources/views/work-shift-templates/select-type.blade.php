@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('work-shift-templates.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-plus-circle text-blue-600 mr-3"></i>Criar Novo Modelo de Jornada
            </h1>
            <p class="text-gray-600 mt-2">Selecione o tipo de jornada que deseja criar</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Jornada Semanal Fixa -->
            <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow overflow-hidden">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white text-center">
                    <i class="fas fa-calendar-week text-5xl mb-3"></i>
                    <h3 class="text-2xl font-bold">Semanal Fixa</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 mb-4 min-h-[80px]">
                        Horários fixos para cada dia da semana. Ideal para o pessoal administrativo.
                    </p>
                    <div class="space-y-2 mb-6 text-sm text-gray-600">
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Horários fixos (ex: 08:00-12:00)</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Diferentes horários por dia da semana</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Até 3 períodos por dia</span>
                        </div>
                    </div>
                    <a href="{{ route('work-shift-templates.create-weekly') }}" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition">
                        <i class="fas fa-arrow-right mr-2"></i>Selecionar
                    </a>
                </div>
            </div>

            <!-- Card 2: Escala de Revezamento -->
            <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow overflow-hidden">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 text-white text-center">
                    <i class="fas fa-sync-alt text-5xl mb-3"></i>
                    <h3 class="text-2xl font-bold">Escala Rotativa</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 mb-4 min-h-[80px]">
                        Escalas de plantão com revezamento cíclico. Ideal para hospital, SAMU e emergências.
                    </p>
                    <div class="space-y-2 mb-6 text-sm text-gray-600">
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Plantões 12x36, 24x72, etc</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Ciclo de revezamento automático</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Calendário de trabalho/folga</span>
                        </div>
                    </div>
                    <a href="{{ route('work-shift-templates.create-rotating') }}" 
                       class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition">
                        <i class="fas fa-arrow-right mr-2"></i>Selecionar
                    </a>
                </div>
            </div>

            <!-- Card 3: Carga Horária Semanal -->
            <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow overflow-hidden">
                <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 text-white text-center">
                    <i class="fas fa-clock text-5xl mb-3"></i>
                    <h3 class="text-2xl font-bold">Carga Horária</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 mb-4 min-h-[80px]">
                        Horários flexíveis com carga horária semanal. Ideal para professores e cargos com jornada variável.
                    </p>
                    <div class="space-y-2 mb-6 text-sm text-gray-600">
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Horários flexíveis (20h, 30h, 40h)</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Soma de horas no período</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                            <span>Não exige horários fixos</span>
                        </div>
                    </div>
                    <a href="{{ route('work-shift-templates.create-flexible') }}" 
                       class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition">
                        <i class="fas fa-arrow-right mr-2"></i>Selecionar
                    </a>
                </div>
            </div>
        </div>

        <!-- Seção de Ajuda -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="font-bold text-blue-900 mb-3 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                Precisa de ajuda para escolher?
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="font-semibold text-blue-900 mb-1">Semanal Fixa</p>
                    <p class="text-blue-700">Use para: Administrativo, Secretarias, Recepção, Operacional</p>
                </div>
                <div>
                    <p class="font-semibold text-blue-900 mb-1">Escala Rotativa</p>
                    <p class="text-blue-700">Use para: Hospital, SAMU, Defesa Civil, Vigilância</p>
                </div>
                <div>
                    <p class="font-semibold text-blue-900 mb-1">Carga Horária</p>
                    <p class="text-blue-700">Use para: Professores, Consultores, Cargos com jornada flexível</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
