@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold mb-6">Novo Estabelecimento</h1>
<div class="bg-white rounded shadow p-6 max-w-2xl">
    <form action="{{ route('establishments.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="col-span-2">
                <label class="block font-medium mb-1">Razão Social *</label>
                <input type="text" name="corporate_name" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="col-span-2">
                <label class="block font-medium mb-1">Nome Fantasia</label>
                <input type="text" name="trade_name" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">CNPJ *</label>
                <input type="text" name="cnpj" required class="w-full border rounded px-3 py-2" placeholder="00.000.000/0000-00">
            </div>
            <div>
                <label class="block font-medium mb-1">Inscrição Estadual</label>
                <input type="text" name="state_registration" class="w-full border rounded px-3 py-2">
            </div>
            <div class="col-span-2">
                <label class="block font-medium mb-1">Logradouro</label>
                <input type="text" name="street" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Número</label>
                <input type="text" name="number" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Complemento</label>
                <input type="text" name="complement" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Bairro</label>
                <input type="text" name="neighborhood" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Cidade</label>
                <input type="text" name="city" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">UF</label>
                <input type="text" name="state" maxlength="2" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">CEP</label>
                <input type="text" name="zip_code" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Telefone</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium mb-1">E-mail</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Salvar</button>
            <a href="{{ route('establishments.index') }}" class="bg-gray-300 px-6 py-2 rounded hover:bg-gray-400">Cancelar</a>
        </div>
    </form>
</div>
@endsection
