<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeRecord extends Model
{
    protected $fillable = [
        'employee_registration_id',
        'recorded_at',
        'record_date',
        'record_time',
        'nsr',
        'record_type',
        'imported_from_afd',
        'afd_file_name',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'record_date' => 'date',
        'imported_from_afd' => 'boolean',
    ];

    /**
     * Relacionamento com vínculo (matrícula)
     */
    public function employeeRegistration(): BelongsTo
    {
        return $this->belongsTo(EmployeeRegistration::class);
    }

    /**
     * Relacionamento com pessoa (através do vínculo)
     */
    public function person(): BelongsTo
    {
        return $this->employeeRegistration->person();
    }

    /**
     * DEPRECATED: Mantido por compatibilidade - usar employeeRegistration()
     */
    public function employee(): BelongsTo
    {
        return $this->employeeRegistration();
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('record_date', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por vínculo (matrícula)
     */
    public function scopeForRegistration($query, $registrationId)
    {
        return $query->where('employee_registration_id', $registrationId);
    }

    /**
     * DEPRECATED: Mantido por compatibilidade - usar scopeForRegistration()
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_registration_id', $employeeId);
    }
}
