<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema de Ponto')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold">Sistema de Ponto</a>
                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('establishments.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Estabelecimentos</a>
                        <a href="{{ route('departments.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Departamentos</a>
                        <a href="{{ route('employees.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Colaboradores</a>
                        <a href="{{ route('afd-imports.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Importar AFD</a>
                        <a href="{{ route('timesheets.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Cartão de Ponto</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
