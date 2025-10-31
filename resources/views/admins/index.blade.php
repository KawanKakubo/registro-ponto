@extends('layouts.main')

@section('title', 'Administradores')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Administradores</h1>
            <p class="text-gray-600 mt-1">Gerencie os usuários com acesso administrativo ao sistema</p>
        </div>
        <a href="{{ route('admins.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
            <i class="fas fa-plus mr-2"></i>Novo Administrador
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total de Administradores</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $admins->total() }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-user-shield text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Administradores Ativos</p>
                    <p class="text-3xl font-bold text-green-600">{{ $admins->where('is_active', true)->count() }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Administradores Inativos</p>
                    <p class="text-3xl font-bold text-red-600">{{ $admins->where('is_active', false)->count() }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-4">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Nome</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">CPF</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Email</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Estabelecimento</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold mr-3">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $admin->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                {{ substr($admin->cpf, 0, 3) }}.{{ substr($admin->cpf, 3, 3) }}.{{ substr($admin->cpf, 6, 3) }}-{{ substr($admin->cpf, 9, 2) }}
                            </td>
                            <td class="py-4 px-4 text-gray-600">{{ $admin->email }}</td>
                            <td class="py-4 px-4 text-gray-600">
                                {{ $admin->establishment ? $admin->establishment->corporate_name : 'Todos' }}
                            </td>
                            <td class="py-4 px-4">
                                @if($admin->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-circle mr-1 text-xs"></i>Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-circle mr-1 text-xs"></i>Inativo
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('admins.edit', $admin) }}" class="text-blue-600 hover:text-blue-800 transition" title="Editar">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admins.destroy', $admin) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este administrador?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Excluir">
                                            <i class="fas fa-trash text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                                <i class="fas fa-user-slash text-4xl mb-3 text-gray-300"></i>
                                <p>Nenhum administrador cadastrado</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($admins->hasPages())
                <div class="mt-6">
                    {{ $admins->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
