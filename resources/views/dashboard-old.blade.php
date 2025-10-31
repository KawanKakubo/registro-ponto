@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Ponto')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-600 mt-2">Sistema de Gerenciamento de Registro de Ponto Eletrônico</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Colaboradores</h3>
        <p class="text-3xl font-bold text-blue-600">{{ \App\Models\Employee::count() }}</p>
        <a href="{{ route('employees.index') }}" class="text-blue-600 hover:underline mt-2 inline-block">Ver todos</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Departamentos</h3>
        <p class="text-3xl font-bold text-green-600">{{ \App\Models\Department::count() }}</p>
        <a href="{{ route('departments.index') }}" class="text-green-600 hover:underline mt-2 inline-block">Ver todos</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Importações AFD</h3>
        <p class="text-3xl font-bold text-purple-600">{{ \App\Models\AfdImport::count() }}</p>
        <a href="{{ route('afd-imports.index') }}" class="text-purple-600 hover:underline mt-2 inline-block">Ver histórico</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Importações CSV</h3>
        <p class="text-3xl font-bold text-orange-600">{{ \App\Models\EmployeeImport::count() }}</p>
        <a href="{{ route('employee-imports.index') }}" class="text-orange-600 hover:underline mt-2 inline-block">Ver histórico</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Registros de Ponto</h3>
        <p class="text-3xl font-bold text-indigo-600">{{ \App\Models\TimeRecord::count() }}</p>
        <a href="{{ route('employees.index') }}" class="text-indigo-600 hover:underline mt-2 inline-block">Ver colaboradores</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Ações Rápidas</h3>
        <div class="space-y-3">
            <a href="{{ route('employees.create') }}" class="block px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-center font-medium transition-colors">
                ➕ Cadastrar Colaborador
            </a>
            <a href="{{ route('employee-imports.create') }}" class="block px-4 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 text-center font-medium transition-colors">
                📊 Importar Colaboradores (CSV)
            </a>
            <a href="{{ route('afd-imports.create') }}" class="block px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 text-center font-medium transition-colors">
                📁 Importar Arquivo AFD
            </a>
            <a href="{{ route('timesheets.index') }}" class="block px-4 py-3 bg-purple-500 text-white rounded-lg hover:bg-purple-600 text-center font-medium transition-colors">
                📋 Gerar Cartão de Ponto
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Últimas Importações</h3>
        @php
            $recentImports = \App\Models\AfdImport::orderBy('created_at', 'desc')->take(5)->get();
        @endphp
        @if($recentImports->count() > 0)
            <div class="space-y-2">
                @foreach($recentImports as $import)
                    <div class="border-b pb-2">
                        <p class="text-sm font-medium">{{ $import->file_name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $import->created_at->format('d/m/Y H:i') }} - 
                            <span class="@if($import->status == 'completed') text-green-600 @elseif($import->status == 'failed') text-red-600 @else text-yellow-600 @endif">
                                {{ ucfirst($import->status) }}
                            </span>
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Nenhuma importação realizada ainda.</p>
        @endif
    </div>
</div>
@endsection
