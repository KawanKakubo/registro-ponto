@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Detalhes da Importação AFD</h1>
    <a href="{{ route('afd-imports.index') }}" class="bg-gray-300 px-4 py-2 rounded">Voltar</a>
</div>

<div class="bg-white rounded shadow p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">Informações da Importação</h2>
    <div class="grid grid-cols-2 gap-4">
        <div><strong>Arquivo:</strong> {{ $afdImport->file_name }}</div>
        <div><strong>Status:</strong> 
            <span class="px-2 py-1 rounded text-sm
                @if($afdImport->status === 'completed') bg-green-100 text-green-800
                @elseif($afdImport->status === 'failed') bg-red-100 text-red-800
                @else bg-yellow-100 text-yellow-800 @endif">
                {{ ucfirst($afdImport->status) }}
            </span>
        </div>
        <div><strong>Data da Importação:</strong> {{ $afdImport->imported_at?->format('d/m/Y H:i:s') ?? $afdImport->created_at->format('d/m/Y H:i:s') }}</div>
        <div><strong>Tamanho do Arquivo:</strong> {{ number_format($afdImport->file_size / 1024, 2) }} KB</div>
        <div><strong>Total de Registros:</strong> {{ $afdImport->total_records ?? 0 }}</div>
        <div><strong>CNPJ:</strong> {{ $afdImport->cnpj ?? '-' }}</div>
        @if($afdImport->start_date && $afdImport->end_date)
        <div class="col-span-2"><strong>Período:</strong> {{ \Carbon\Carbon::parse($afdImport->start_date)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($afdImport->end_date)->format('d/m/Y') }}</div>
        @endif
    </div>
</div>

@if($afdImport->error_message)
<div class="bg-red-50 border border-red-200 rounded shadow p-6 mb-6">
    <h2 class="text-lg font-semibold text-red-800 mb-2">Mensagem de Erro</h2>
    <pre class="text-sm text-red-700 whitespace-pre-wrap">{{ $afdImport->error_message }}</pre>
</div>
@endif

@if($afdImport->status === 'completed')
<div class="bg-white rounded shadow p-6">
    <h2 class="text-lg font-semibold mb-4">Registros de Ponto Importados</h2>
    <div class="mb-4">
        <p class="text-sm text-gray-600">Este arquivo foi processado com sucesso. Os registros estão disponíveis na listagem de funcionários.</p>
    </div>
</div>
@endif
@endsection
