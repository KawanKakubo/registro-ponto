<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartão de Ponto - {{ $employee->full_name }}</title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .info-section { margin-bottom: 15px; }
        .info-row { display: flex; margin-bottom: 5px; }
        .info-label { font-weight: bold; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; }
        th { background-color: #f0f0f0; padding: 8px; text-align: center; font-weight: bold; }
        td { padding: 5px; text-align: center; }
        .day-col { width: 100px; text-align: left; padding-left: 10px; }
        .time-col { width: 60px; }
        .hours-col { width: 80px; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .signature-section { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; }
        .signature-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Imprimir</button>
        <a href="{{ route('timesheets.index') }}" style="background: #6b7280; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin-left: 10px;">Voltar</a>
    </div>

    <div class="header">
        <h1>CARTÃO DE PONTO</h1>
        <p>{{ $establishment->corporate_name }}</p>
        <p>CNPJ: {{ $establishment->cnpj }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Nome:</span>
            <span>{{ $employee->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">CPF:</span>
            <span>{{ $employee->cpf }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">PIS/PASEP:</span>
            <span>{{ $employee->pis_pasep }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">CTPS:</span>
            <span>{{ $employee->ctps }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Data de Admissão:</span>
            <span>{{ \Carbon\Carbon::parse($employee->admission_date)->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Função:</span>
            <span>{{ $employee->position }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Departamento:</span>
            <span>{{ $employee->department->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Período:</span>
            <span>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="day-col">Dia</th>
                <th class="time-col">Ent 1</th>
                <th class="time-col">Sai 1</th>
                <th class="time-col">Ent 2</th>
                <th class="time-col">Sai 2</th>
                <th class="time-col">Ent 3</th>
                <th class="time-col">Sai 3</th>
                <th class="time-col">Ent 4</th>
                <th class="time-col">Sai 4</th>
                <th class="hours-col">Horas Extras</th>
                <th class="hours-col">Faltas/Atrasos</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalOvertimeMinutes = 0;
                $totalAbsenceMinutes = 0;
                $daysOfWeek = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            @endphp

            @foreach($dailyRecords as $date => $records)
                @php
                    $carbonDate = \Carbon\Carbon::parse($date);
                    $dayOfWeek = $daysOfWeek[$carbonDate->dayOfWeek];
                    $punches = $records->sortBy('record_time')->pluck('record_time')->map(function($time) {
                        return substr($time, 0, 5);
                    })->toArray();
                    $overtime = $calculations[$date]['overtime'] ?? 0;
                    $absence = $calculations[$date]['absence'] ?? 0;
                    $totalOvertimeMinutes += $overtime;
                    $totalAbsenceMinutes += $absence;
                @endphp
                
                <tr>
                    <td class="day-col">{{ $carbonDate->format('d/m/y') }} ({{ $dayOfWeek }})</td>
                    <td class="time-col">{{ $punches[0] ?? '' }}</td>
                    <td class="time-col">{{ $punches[1] ?? '' }}</td>
                    <td class="time-col">{{ $punches[2] ?? '' }}</td>
                    <td class="time-col">{{ $punches[3] ?? '' }}</td>
                    <td class="time-col">{{ $punches[4] ?? '' }}</td>
                    <td class="time-col">{{ $punches[5] ?? '' }}</td>
                    <td class="time-col">{{ $punches[6] ?? '' }}</td>
                    <td class="time-col">{{ $punches[7] ?? '' }}</td>
                    <td class="hours-col">{{ $overtime > 0 ? gmdate('H:i', $overtime * 60) : '' }}</td>
                    <td class="hours-col">{{ $absence > 0 ? gmdate('H:i', $absence * 60) : '' }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="9" style="text-align: right; padding-right: 10px;"><strong>TOTAIS:</strong></td>
                <td class="hours-col"><strong>{{ gmdate('H:i', $totalOvertimeMinutes * 60) }}</strong></td>
                <td class="hours-col"><strong>{{ gmdate('H:i', $totalAbsenceMinutes * 60) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <p><strong>Observações:</strong></p>
        <div style="border: 1px solid #000; min-height: 60px; padding: 10px;"></div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Assinatura do Empregado</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Assinatura do Empregador</div>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 10px;">
        <p>Documento gerado em conformidade com a Portaria MTP nº 671/2021</p>
        <p>Emitido em: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
