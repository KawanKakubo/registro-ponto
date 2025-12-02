@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-users me-2"></i>
                        Relação de Colaboradores
                    </h1>
                    <p class="text-muted mb-0">Total de registros: <strong>{{ $registrations->count() }}</strong></p>
                </div>
                <div>
                    <a href="{{ route('reports.employees.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar aos Filtros
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>Imprimir
                    </button>
                    <form action="{{ route('reports.employees.generate') }}" method="POST" class="d-inline">
                        @csrf
                        @foreach($request->except('format') as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <button type="submit" name="format" value="csv" class="btn btn-success">
                            <i class="fas fa-file-csv me-2"></i>Exportar CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($registrations->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Nenhum colaborador encontrado com os filtros selecionados.
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                @if(in_array('full_name', $fields))
                                    <th>Nome Completo</th>
                                @endif
                                @if(in_array('cpf', $fields))
                                    <th>CPF</th>
                                @endif
                                @if(in_array('rg', $fields))
                                    <th>RG</th>
                                @endif
                                @if(in_array('birth_date', $fields))
                                    <th>Data de Nascimento</th>
                                @endif
                                @if(in_array('gender', $fields))
                                    <th>Sexo</th>
                                @endif
                                @if(in_array('email', $fields))
                                    <th>E-mail</th>
                                @endif
                                @if(in_array('phone', $fields))
                                    <th>Telefone</th>
                                @endif
                                @if(in_array('pis_pasep', $fields))
                                    <th>PIS/PASEP</th>
                                @endif
                                @if(in_array('ctps', $fields))
                                    <th>CTPS</th>
                                @endif
                                @if(in_array('matricula', $fields))
                                    <th>Matrícula</th>
                                @endif
                                @if(in_array('position', $fields))
                                    <th>Cargo</th>
                                @endif
                                @if(in_array('establishment', $fields))
                                    <th>Estabelecimento</th>
                                @endif
                                @if(in_array('department', $fields))
                                    <th>Departamento</th>
                                @endif
                                @if(in_array('admission_date', $fields))
                                    <th>Data de Admissão</th>
                                @endif
                                @if(in_array('termination_date', $fields))
                                    <th>Data de Desligamento</th>
                                @endif
                                @if(in_array('status', $fields))
                                    <th>Status</th>
                                @endif
                                @if(in_array('work_shift', $fields))
                                    <th>Jornada de Trabalho</th>
                                @endif
                                @if(in_array('address', $fields))
                                    <th>Endereço</th>
                                @endif
                                @if(in_array('city', $fields))
                                    <th>Cidade</th>
                                @endif
                                @if(in_array('state', $fields))
                                    <th>Estado</th>
                                @endif
                                @if(in_array('zip_code', $fields))
                                    <th>CEP</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registrations as $registration)
                                <tr>
                                    @if(in_array('full_name', $fields))
                                        <td>{{ $registration->person->full_name }}</td>
                                    @endif
                                    @if(in_array('cpf', $fields))
                                        <td>{{ $registration->person->cpf_formatted ?? $registration->person->cpf }}</td>
                                    @endif
                                    @if(in_array('rg', $fields))
                                        <td>{{ $registration->person->rg ?? '-' }}</td>
                                    @endif
                                    @if(in_array('birth_date', $fields))
                                        <td>{{ $registration->person->birth_date ? $registration->person->birth_date->format('d/m/Y') : '-' }}</td>
                                    @endif
                                    @if(in_array('gender', $fields))
                                        <td>
                                            @if($registration->person->gender === 'M')
                                                Masculino
                                            @elseif($registration->person->gender === 'F')
                                                Feminino
                                            @else
                                                Outro
                                            @endif
                                        </td>
                                    @endif
                                    @if(in_array('email', $fields))
                                        <td>{{ $registration->person->email ?? '-' }}</td>
                                    @endif
                                    @if(in_array('phone', $fields))
                                        <td>{{ $registration->person->phone ?? '-' }}</td>
                                    @endif
                                    @if(in_array('pis_pasep', $fields))
                                        <td>{{ $registration->person->pis_pasep_formatted ?? $registration->person->pis_pasep ?? '-' }}</td>
                                    @endif
                                    @if(in_array('ctps', $fields))
                                        <td>{{ $registration->person->ctps ?? '-' }}</td>
                                    @endif
                                    @if(in_array('matricula', $fields))
                                        <td>{{ $registration->matricula ?? '-' }}</td>
                                    @endif
                                    @if(in_array('position', $fields))
                                        <td>{{ $registration->position ?? '-' }}</td>
                                    @endif
                                    @if(in_array('establishment', $fields))
                                        <td>{{ $registration->establishment->corporate_name ?? '-' }}</td>
                                    @endif
                                    @if(in_array('department', $fields))
                                        <td>{{ $registration->department->name ?? '-' }}</td>
                                    @endif
                                    @if(in_array('admission_date', $fields))
                                        <td>{{ $registration->admission_date ? $registration->admission_date->format('d/m/Y') : '-' }}</td>
                                    @endif
                                    @if(in_array('termination_date', $fields))
                                        <td>{{ $registration->termination_date ? $registration->termination_date->format('d/m/Y') : '-' }}</td>
                                    @endif
                                    @if(in_array('status', $fields))
                                        <td>
                                            @if($registration->termination_date)
                                                <span class="badge bg-danger">Desligado</span>
                                            @else
                                                <span class="badge bg-success">Ativo</span>
                                            @endif
                                        </td>
                                    @endif
                                    @if(in_array('work_shift', $fields))
                                        <td>
                                            @php
                                                $assignment = $registration->currentWorkShiftAssignment;
                                            @endphp
                                            {{ $assignment ? $assignment->template->name : '-' }}
                                        </td>
                                    @endif
                                    @if(in_array('address', $fields))
                                        <td>{{ $registration->person->address ?? '-' }}</td>
                                    @endif
                                    @if(in_array('city', $fields))
                                        <td>{{ $registration->person->city ?? '-' }}</td>
                                    @endif
                                    @if(in_array('state', $fields))
                                        <td>{{ $registration->person->state ?? '-' }}</td>
                                    @endif
                                    @if(in_array('zip_code', $fields))
                                        <td>{{ $registration->person->zip_code ?? '-' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
@media print {
    .btn, .no-print {
        display: none !important;
    }
    
    body {
        font-size: 10pt;
    }
    
    .table {
        font-size: 9pt;
    }
    
    .table th, .table td {
        padding: 0.3rem !important;
    }
}
</style>
@endsection
