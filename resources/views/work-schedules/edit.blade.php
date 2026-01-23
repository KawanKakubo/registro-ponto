@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('employees.work-schedules.index', $employee) }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-edit text-yellow-600 mr-3"></i>Editar Escala de Trabalho
            </h1>
            <p class="text-gray-600 mt-2">
                <i class="fas fa-user mr-1"></i>{{ $employee->full_name }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8 max-w-4xl">
        <form action="{{ route('employees.work-schedules.update', [$employee, $workSchedule]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-calendar-day text-blue-600 mr-1"></i>Dia da Semana *
                </label>
                <select name="day_of_week" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Selecione o dia...</option>
                    <option value="0" {{ $workSchedule->day_of_week == 0 ? 'selected' : '' }}>🔴 Domingo</option>
                    <option value="1" {{ $workSchedule->day_of_week == 1 ? 'selected' : '' }}>🔵 Segunda-feira</option>
                    <option value="2" {{ $workSchedule->day_of_week == 2 ? 'selected' : '' }}>🟢 Terça-feira</option>
                    <option value="3" {{ $workSchedule->day_of_week == 3 ? 'selected' : '' }}>🟡 Quarta-feira</option>
                    <option value="4" {{ $workSchedule->day_of_week == 4 ? 'selected' : '' }}>🟣 Quinta-feira</option>
                    <option value="5" {{ $workSchedule->day_of_week == 5 ? 'selected' : '' }}>🟠 Sexta-feira</option>
                    <option value="6" {{ $workSchedule->day_of_week == 6 ? 'selected' : '' }}>🟤 Sábado</option>
                </select>
                @error('day_of_week')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="border-t-2 border-gray-200 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                        <span class="text-blue-600 font-bold">1</span>
                    </div>
                    Período 1
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sign-in-alt text-green-600 mr-1"></i>Entrada 1
                        </label>
                        <input type="time" name="entry_1" value="{{ $workSchedule->entry_1 ? \Carbon\Carbon::parse($workSchedule->entry_1)->format('H:i') : '' }}" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sign-out-alt text-red-600 mr-1"></i>Saída 1
                        </label>
                        <input type="time" name="exit_1" value="{{ $workSchedule->exit_1 ? \Carbon\Carbon::parse($workSchedule->exit_1)->format('H:i') : '' }}" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
            
            <div class="border-t-2 border-gray-200 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-2">
                        <span class="text-purple-600 font-bold">2</span>
                    </div>
                    Período 2 <span class="text-sm font-normal text-gray-500 ml-2">(opcional)</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sign-in-alt text-green-600 mr-1"></i>Entrada 2
                        </label>
                        <input type="time" name="entry_2" value="{{ $workSchedule->entry_2 ? \Carbon\Carbon::parse($workSchedule->entry_2)->format('H:i') : '' }}" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sign-out-alt text-red-600 mr-1"></i>Saída 2
                        </label>
                        <input type="time" name="exit_2" value="{{ $workSchedule->exit_2 ? \Carbon\Carbon::parse($workSchedule->exit_2)->format('H:i') : '' }}" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
            
            <div class="border-t-2 border-gray-200 pt-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-2">
                        <span class="text-yellow-600 font-bold">3</span>
                    </div>
                    Período 3 <span class="text-sm font-normal text-gray-500 ml-2">(opcional)</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sign-in-alt text-green-600 mr-1"></i>Entrada 3
                        </label>
                        <input type="time" name="entry_3" value="{{ $workSchedule->entry_3 ? \Carbon\Carbon::parse($workSchedule->entry_3)->format('H:i') : '' }}" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sign-out-alt text-red-600 mr-1"></i>Saída 3
                        </label>
                        <input type="time" name="exit_3" value="{{ $workSchedule->exit_3 ? \Carbon\Carbon::parse($workSchedule->exit_3)->format('H:i') : '' }}" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-end space-x-4 pt-6 border-t-2 border-gray-200">
                <a href="{{ route('employees.work-schedules.index', $employee) }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i>Atualizar Escala
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
