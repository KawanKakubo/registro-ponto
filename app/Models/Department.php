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
     * Relacionamento com colaboradores
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
