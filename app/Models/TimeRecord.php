<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeRecord extends Model
{
    protected $fillable = [
        'employee_id',
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
     * Relacionamento com colaborador
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('record_date', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por colaborador
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
