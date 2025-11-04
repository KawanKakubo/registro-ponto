<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Establishment extends Model
{
    protected $fillable = [
        'corporate_name',
        'trade_name',
        'cnpj',
        'state_registration',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
    ];

    /**
     * Relacionamento com departamentos
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Relacionamento com colaboradores (DEPRECATED - use employeeRegistrations)
     * @deprecated Usar employeeRegistrations() ao invés deste método
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Relacionamento com vínculos de colaboradores (EmployeeRegistrations)
     * Este é o relacionamento atual que deve ser usado
     */
    public function employeeRegistrations(): HasMany
    {
        return $this->hasMany(EmployeeRegistration::class);
    }

    /**
     * Relacionamento com vínculos ativos apenas
     */
    public function activeRegistrations(): HasMany
    {
        return $this->hasMany(EmployeeRegistration::class)->where('status', 'active');
    }

    /**
     * Relacionamento com usuários
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Formatar CNPJ
     */
    public function getFormattedCnpjAttribute(): string
    {
        return $this->cnpj;
    }
}
