@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">📋 Modelos de Jornada</h1>
        <p class="text-gray-600 mt-1">Gerencie os modelos de jornada de trabalho reutilizáveis</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('work-shift-templates.bulk-assign') }}" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg hover:from-green-600 hover:to-green-700 shadow-md hover:shadow-lg transition-all font-bold">
            🚀 Aplicação em Massa
        </a>
        <a href="{{ route('work-shift-templates.create') }}" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 shadow-md hover:shadow-lg transition-all font-bold">
            ➕ Novo Modelo
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
@endif

<!-- Templates Pré-Configurados -->
<div class="mb-8">
    <h2 class="text-xl font-bold text-gray-700 mb-4">⭐ Modelos Pré-Configurados (Prefeitura de Assaí)</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($templates->where('is_preset', true) as $template)
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg shadow-md hover:shadow-xl transition-shadow p-6">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">{{ $template->name }}</h3>
                        <span class="inline-block bg-blue-500 text-white text-xs px-2 py-1 rounded mt-1">⭐ PRÉ-CONFIGURADO</span>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">{{ $template->weekly_hours }}h</div>
                        <div class="text-xs text-gray-600">por semana</div>
                    </div>
                </div>
                
                @if($template->type === 'weekly' && $template->weeklySchedules->count() > 0)
                    <div class="bg-white rounded-lg p-3 mb-3 text-sm">
                        @foreach($template->weeklySchedules->where('is_work_day', true)->take(2) as $schedule)
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-medium text-gray-700">{{ $schedule->day_short_name }}:</span>
                                <span class="text-gray-600">
                                    {{ $schedule->entry_1 ? \Carbon\Carbon::parse($schedule->entry_1)->format('H:i') : '' }} - 
                                    {{ $schedule->exit_1 ? \Carbon\Carbon::parse($schedule->exit_1)->format('H:i') : '' }}
                                    @if($schedule->entry_2)
                                        | {{ \Carbon\Carbon::parse($schedule->entry_2)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($schedule->exit_2)->format('H:i') }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                        @if($template->weeklySchedules->where('is_work_day', true)->count() > 2)
                            <div class="text-xs text-gray-500 text-center mt-1">+ {{ $template->weeklySchedules->where('is_work_day', true)->count() - 2 }} dias</div>
                        @endif
                    </div>
                @endif

                <div class="flex items-center justify-between pt-3 border-t">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">{{ $template->employees_count }}</span> colaborador(es)
                    </div>
                    <div class="text-xs text-gray-500">
                        🔒 Protegido
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Templates Personalizados -->
@if($templates->where('is_preset', false)->count() > 0)
<div>
    <h2 class="text-xl font-bold text-gray-700 mb-4">🔧 Modelos Personalizados</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-6 py-3 font-medium text-gray-700">Nome</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-700">Tipo</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-700">Carga Horária</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-700">Colaboradores</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-700">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates->where('is_preset', false) as $template)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $template->name }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block px-2 py-1 text-xs rounded {{ $template->type === 'weekly' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $template->type === 'weekly' ? 'Semanal' : 'Escala Rotativa' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center font-bold">{{ $template->weekly_hours }}h</td>
                    <td class="px-6 py-4 text-center">{{ $template->employees_count }}</td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('work-shift-templates.edit', $template) }}" class="text-yellow-600 hover:underline mr-3">✏️ Editar</a>
                        @if($template->employees_count == 0)
                            <form action="{{ route('work-shift-templates.destroy', $template) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Tem certeza que deseja excluir este template?')">🗑️ Excluir</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <p class="text-gray-600">Nenhum modelo personalizado criado ainda.</p>
        <p class="text-gray-500 text-sm mt-2">Clique em "Novo Modelo" para criar um modelo customizado.</p>
    </div>
@endif

@endsection
