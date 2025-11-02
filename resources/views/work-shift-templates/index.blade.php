@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-business-time text-blue-600 mr-3"></i>Modelos de Jornada
            </h1>
            <p class="text-gray-600 mt-2">Gerencie os modelos de jornada de trabalho reutilizáveis</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('work-shift-templates.bulk-assign') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition">
                <i class="fas fa-users-cog mr-2"></i>Aplicação em Massa
            </a>
            <a href="{{ route('work-shift-templates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition">
                <i class="fas fa-plus mr-2"></i>Novo Modelo
            </a>
        </div>
    </div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
@endif

    <!-- Modelos de Jornada -->
    @if($templates->count() > 0)
    <div>
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
                @foreach($templates as $template)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $template->name }}</div>
                        @if($template->description)
                            <div class="text-sm text-gray-500">{{ $template->description }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($template->type === 'weekly')
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-calendar-week mr-1"></i> Semanal Fixa
                            </span>
                        @elseif($template->type === 'rotating_shift')
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                <i class="fas fa-sync-alt mr-1"></i> Revezamento
                            </span>
                        @elseif($template->type === 'weekly_hours')
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-clock mr-1"></i> Carga Horária
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($template->type === 'weekly' || $template->type === 'weekly_hours')
                            <span class="font-bold text-gray-900">{{ $template->weekly_hours ?? ($template->flexibleHours->weekly_hours_required ?? 'N/A') }}h</span>
                            <span class="text-xs text-gray-500 block">
                                {{ $template->type === 'weekly_hours' ? ($template->flexibleHours->period_type_formatted ?? 'semanal') : 'semanal' }}
                            </span>
                        @elseif($template->type === 'rotating_shift')
                            <span class="font-bold text-gray-900">{{ $template->rotatingRule->work_days }}x{{ $template->rotatingRule->rest_days }}</span>
                            <span class="text-xs text-gray-500 block">ciclo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $template->employees_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-500' }} font-semibold">
                            {{ $template->employees_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('work-shift-templates.edit', $template) }}" class="text-yellow-600 hover:text-yellow-800 mr-3" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($template->employees_count == 0)
                            <form action="{{ route('work-shift-templates.destroy', $template) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este modelo?')">
                                    <i class="fas fa-trash"></i>
                                </button>
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
            <i class="fas fa-cog text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-600 text-lg">Nenhum modelo personalizado criado ainda.</p>
            <p class="text-gray-500 text-sm mt-2">Clique em "Novo Modelo" para criar um modelo customizado.</p>
        </div>
    @endif
</div>
@endsection
