@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">{{ $establishment->corporate_name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('establishments.edit', $establishment) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">Editar</a>
        <a href="{{ route('establishments.index') }}" class="bg-gray-300 px-4 py-2 rounded">Voltar</a>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Informações do Estabelecimento</h2>
    <div class="grid grid-cols-2 gap-4">
        <div><strong>Razão Social:</strong> {{ $establishment->corporate_name }}</div>
        <div><strong>Nome Fantasia:</strong> {{ $establishment->trade_name ?? '-' }}</div>
        <div><strong>CNPJ:</strong> {{ $establishment->cnpj }}</div>
        <div><strong>Inscrição Estadual:</strong> {{ $establishment->state_registration ?? '-' }}</div>
        <div class="col-span-2"><strong>Endereço:</strong> {{ $establishment->street }}, {{ $establishment->number }}</div>
        <div><strong>Complemento:</strong> {{ $establishment->complement ?? '-' }}</div>
        <div><strong>Bairro:</strong> {{ $establishment->neighborhood ?? '-' }}</div>
        <div><strong>Cidade:</strong> {{ $establishment->city ?? '-' }}</div>
        <div><strong>UF:</strong> {{ $establishment->state ?? '-' }}</div>
        <div><strong>CEP:</strong> {{ $establishment->zip_code ?? '-' }}</div>
        <div><strong>Telefone:</strong> {{ $establishment->phone ?? '-' }}</div>
        <div><strong>E-mail:</strong> {{ $establishment->email ?? '-' }}</div>
    </div>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Departamentos</h2>
        <a href="{{ route('departments.create') }}" class="bg-green-500 text-white px-4 py-2 rounded text-sm">+ Novo Departamento</a>
    </div>
    @if($establishment->departments->count() > 0)
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Nome</th>
                    <th class="text-left py-2">Responsável</th>
                    <th class="text-left py-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($establishment->departments as $dept)
                <tr class="border-b">
                    <td class="py-2">{{ $dept->name }}</td>
                    <td class="py-2">{{ $dept->responsible ?? '-' }}</td>
                    <td class="py-2">
                        <a href="{{ route('departments.show', $dept) }}" class="text-blue-500">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500">Nenhum departamento cadastrado.</p>
    @endif
</div>

<div class="bg-white rounded shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Funcionários</h2>
        <a href="{{ route('employees.create') }}" class="bg-green-500 text-white px-4 py-2 rounded text-sm">+ Novo Funcionário</a>
    </div>
    @if($establishment->employees->count() > 0)
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Nome</th>
                    <th class="text-left py-2">CPF</th>
                    <th class="text-left py-2">Departamento</th>
                    <th class="text-left py-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($establishment->employees as $emp)
                <tr class="border-b">
                    <td class="py-2">{{ $emp->full_name }}</td>
                    <td class="py-2">{{ $emp->cpf }}</td>
                    <td class="py-2">{{ $emp->department->name ?? '-' }}</td>
                    <td class="py-2">
                        <a href="{{ route('employees.show', $emp) }}" class="text-blue-500">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500">Nenhum funcionário cadastrado.</p>
    @endif
</div>
@endsection
