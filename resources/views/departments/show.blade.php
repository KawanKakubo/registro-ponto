@extends('layouts.main')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-sitemap text-purple-600 mr-3"></i>{{ $department->name }}
                </h1>
                <p class="text-gray-600 mt-2">
                    <i class="fas fa-building mr-1"></i>{{ $department->establishment->corporate_name }}
                </p>
            </div>
        </div>
        <a href="{{ route('departments.edit', $department) }}" class="inline-flex items-center px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition shadow-lg">
            <i class="fas fa-edit mr-2"></i>Editar Departamento
        </a>
    </div>

    <!-- Department Info -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Informações do Departamento
            </h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <div class="flex items-start">
                <div class="p-2 bg-purple-100 rounded-lg mr-3">
                    <i class="fas fa-sitemap text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Nome do Departamento</p>
                    <p class="font-semibold text-gray-900">{{ $department->name }}</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-user-tie text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Responsável</p>
                    <p class="font-semibold text-gray-900">{{ $department->responsible ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-start md:col-span-2">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-building text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Estabelecimento</p>
                    <p class="font-semibold text-gray-900">{{ $department->establishment->corporate_name }}</p>
                    <p class="text-sm text-gray-600 mt-1">CNPJ: {{ $department->establishment->cnpj }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-users text-blue-600 mr-2"></i>Colaboradores
                <span class="text-sm font-normal text-gray-600 ml-2">({{ $department->employees->count() }})</span>
            </h3>
            <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition text-sm">
                <i class="fas fa-plus mr-2"></i>Novo Colaborador
            </a>
        </div>
        <div class="p-6">
            @if($department->employees->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                                    <i class="fas fa-user mr-1"></i>Nome
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                                    <i class="fas fa-id-card mr-1"></i>CPF
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">
                                    <i class="fas fa-briefcase mr-1"></i>Cargo
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($department->employees as $emp)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3">
                                            {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $emp->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $emp->cpf }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $emp->position ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                    <a href="{{ route('employees.show', $emp) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 mb-4">Nenhum colaborador cadastrado neste departamento</p>
                    <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i>Cadastrar Primeiro Colaborador
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
