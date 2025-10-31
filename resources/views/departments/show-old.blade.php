@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">{{ $department->name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('departments.edit', $department) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">Editar</a>
        <a href="{{ route('departments.index') }}" class="bg-gray-300 px-4 py-2 rounded">Voltar</a>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Informações do Departamento</h2>
    <div class="grid grid-cols-2 gap-4">
        <div><strong>Nome:</strong> {{ $department->name }}</div>
        <div><strong>Responsável:</strong> {{ $department->responsible ?? '-' }}</div>
        <div class="col-span-2"><strong>Estabelecimento:</strong> {{ $department->establishment->corporate_name }}</div>
    </div>
</div>

<div class="bg-white rounded shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Funcionários</h2>
        <a href="{{ route('employees.create') }}" class="bg-green-500 text-white px-4 py-2 rounded text-sm">+ Novo Funcionário</a>
    </div>
    @if($department->employees->count() > 0)
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Nome</th>
                    <th class="text-left py-2">CPF</th>
                    <th class="text-left py-2">Cargo</th>
                    <th class="text-left py-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($department->employees as $emp)
                <tr class="border-b">
                    <td class="py-2">{{ $emp->full_name }}</td>
                    <td class="py-2">{{ $emp->cpf }}</td>
                    <td class="py-2">{{ $emp->position ?? '-' }}</td>
                    <td class="py-2">
                        <a href="{{ route('employees.show', $emp) }}" class="text-blue-500">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500">Nenhum funcionário cadastrado neste departamento.</p>
    @endif
</div>
@endsection
