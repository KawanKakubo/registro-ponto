<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSchedule extends Model
{
    protected $fillable = [
        'employee_registration_id',
        'source_template_id',
        'day_of_week',
        'entry_1',
        'exit_1',
        'entry_2',
        'exit_2',
        'entry_3',
        'exit_3',
        'total_hours',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    /**
     * Relacionamento com vínculo (matrícula)
     */
    public function employeeRegistration(): BelongsTo
    {
        return $this->belongsTo(EmployeeRegistration::class);
    }

    /**
     * DEPRECATED: Mantido por compatibilidade - usar employeeRegistration()
     */
    public function employee(): BelongsTo
    {
        return $this->employeeRegistration();
    }

    /**
     * Retorna o nome do dia da semana
     */
    public function getDayNameAttribute(): string
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return $days[$this->day_of_week] ?? '';
    }
}
