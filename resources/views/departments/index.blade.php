@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-sitemap text-blue-600 mr-3"></i>Departamentos
            </h1>
            <p class="text-gray-600 mt-2">Gerencie os departamentos da empresa</p>
        </div>
        <a href="{{ route('departments.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition">
            <i class="fas fa-plus mr-2"></i>Novo Departamento
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Total</p>
                    <p class="text-3xl font-bold">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-sitemap text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Com Vínculos</p>
                    <p class="text-3xl font-bold">{{ $stats['with_registrations'] }}</p>
                </div>
                <div class="bg-green-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-user-check text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-900 text-sm font-medium mb-1">Total Vínculos</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['total_registrations'] }}</p>
                </div>
                <div class="bg-yellow-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-users text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Vínculos Ativos</p>
                    <p class="text-3xl font-bold">{{ $stats['active_registrations'] }}</p>
                </div>
                <div class="bg-purple-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-user-friends text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Estabelecimentos</p>
                    <p class="text-3xl font-bold">{{ $stats['establishments'] }}</p>
                </div>
                <div class="bg-orange-700 bg-opacity-50 rounded-full p-4">
                    <i class="fas fa-building text-2xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Departamento</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Estabelecimento</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Responsável</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-700">Vínculos</th>
                        <th class="text-right px-6 py-4 font-semibold text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($departments as $dept)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                                            <div>
                                <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center text-white font-bold mr-3 shadow">
                                    {{ strtoupper(substr($dept->name, 0, 1)) }}
                                </div>
                                <div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $dept->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-building text-gray-400 mr-2"></i>
                                {{ $dept->establishment->corporate_name }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($dept->responsible)
                                <div class="flex items-center text-gray-700">
                                    <i class="fas fa-user-tie text-gray-400 mr-2"></i>
                                    {{ $dept->responsible }}
                                </div>
                            @else
                                <span class="text-gray-400 italic">Não informado</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-users mr-2"></i>
                                    {{ $dept->employee_registrations_count }}
                                </span>
                                @if($dept->active_registrations_count > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800" title="Vínculos Ativos">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $dept->active_registrations_count }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('departments.show', $dept) }}" class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded transition" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('departments.edit', $dept) }}" class="text-yellow-600 hover:text-yellow-800 p-2 hover:bg-yellow-50 rounded transition" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este departamento?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded transition" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-sitemap text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">Nenhum departamento cadastrado</p>
                            <a href="{{ route('departments.create') }}" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-plus mr-2"></i>Cadastrar primeiro departamento
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
