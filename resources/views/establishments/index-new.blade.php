@extends('layouts.main')

@section('title', 'Estabelecimentos')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Estabelecimentos</h1>
            <p class="text-gray-600 mt-1">Gerencie os estabelecimentos cadastrados no sistema</p>
        </div>
        <a href="{{ route('establishments.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
            <i class="fas fa-plus mr-2"></i>Novo Estabelecimento
        </a>
    </div>

    <!-- Stats Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total de Estabelecimentos</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $establishments->total() }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-building text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Departamentos</p>
                    <p class="text-3xl font-bold text-green-600">{{ \App\Models\Department::count() }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-sitemap text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Colaboradores</p>
                    <p class="text-3xl font-bold text-purple-600">{{ \App\Models\Employee::count() }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-4">
                    <i class="fas fa-users text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Razão Social</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Nome Fantasia</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">CNPJ</th>
                        <th class="text-left py-4 px-6 font-semibold text-gray-700">Cidade/UF</th>
                        <th class="text-center py-4 px-6 font-semibold text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($establishments as $establishment)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-4 px-6">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold mr-3">
                                    {{ strtoupper(substr($establishment->corporate_name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $establishment->corporate_name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-gray-600">{{ $establishment->trade_name ?? '—' }}</td>
                        <td class="py-4 px-6 text-gray-600 font-mono">{{ $establishment->cnpj }}</td>
                        <td class="py-4 px-6 text-gray-600">
                            {{ $establishment->city ?? '—' }}{{ $establishment->state ? '/' . $establishment->state : '' }}
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('establishments.show', $establishment) }}" class="text-blue-600 hover:text-blue-800 transition" title="Ver Detalhes">
                                    <i class="fas fa-eye text-lg"></i>
                                </a>
                                <a href="{{ route('establishments.edit', $establishment) }}" class="text-yellow-600 hover:text-yellow-800 transition" title="Editar">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <form method="POST" action="{{ route('establishments.destroy', $establishment) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este estabelecimento?');">
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
                        <td colspan="5" class="py-12 px-6 text-center text-gray-500">
                            <i class="fas fa-building text-5xl mb-4 text-gray-300"></i>
                            <p class="text-lg">Nenhum estabelecimento cadastrado</p>
                            <a href="{{ route('establishments.create') }}" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                                Cadastrar primeiro estabelecimento <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($establishments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $establishments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
