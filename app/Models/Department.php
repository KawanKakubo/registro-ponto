<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'establishment_id',
        'name',
        'responsible',
    ];

    /**
     * Relacionamento com estabelecimento
     */
    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    /**
     * Relacionamento com colaboradores (DEPRECATED)
     * 
     * @deprecated Use employeeRegistrations() instead
     * @see employeeRegistrations()
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Relacionamento com vínculos de colaboradores
     * 
     * Um departamento pode ter vários vínculos de colaboradores
     */
    public function employeeRegistrations(): HasMany
    {
        return $this->hasMany(EmployeeRegistration::class);
    }

    /**
     * Relacionamento com vínculos ativos
     */
    public function activeRegistrations(): HasMany
    {
        return $this->hasMany(EmployeeRegistration::class)
            ->where('status', 'active');
    }
}
