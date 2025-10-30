<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'establishment_id',
        'department_id',
        'full_name',
        'cpf',
        'pis_pasep',
        'ctps',
        'admission_date',
        'position',
        'status',
    ];

    protected $casts = [
        'admission_date' => 'date',
    ];

    /**
     * Relacionamento com estabelecimento
     */
    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    /**
     * Relacionamento com departamento
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relacionamento com horários de trabalho
     */
    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    /**
     * Relacionamento com registros de ponto
     */
    public function timeRecords(): HasMany
    {
        return $this->hasMany(TimeRecord::class);
    }

    /**
     * Mutator para formatar CPF ao salvar
     */
    public function setCpfAttribute($value): void
    {
        // Remove tudo que não é número
        $cpf = preg_replace('/[^0-9]/', '', $value);
        
        // Formata: 000.000.000-00
        if (strlen($cpf) == 11) {
            $this->attributes['cpf'] = sprintf(
                '%s.%s.%s-%s',
                substr($cpf, 0, 3),
                substr($cpf, 3, 3),
                substr($cpf, 6, 3),
                substr($cpf, 9, 2)
            );
        } else {
            $this->attributes['cpf'] = $value;
        }
    }

    /**
     * Mutator para formatar PIS ao salvar
     */
    public function setPisPasepAttribute($value): void
    {
        if (!$value) {
            $this->attributes['pis_pasep'] = null;
            return;
        }

        // Remove tudo que não é número
        $pis = preg_replace('/[^0-9]/', '', $value);
        
        // Formata: 000.00000.00-0
        if (strlen($pis) == 11) {
            $this->attributes['pis_pasep'] = sprintf(
                '%s.%s.%s-%s',
                substr($pis, 0, 3),
                substr($pis, 3, 5),
                substr($pis, 8, 2),
                substr($pis, 10, 1)
            );
        } else {
            $this->attributes['pis_pasep'] = $value;
        }
    }

    /**
     * Scope para colaboradores ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Accessor para obter CPF sem formatação
     */
    public function getCpfRawAttribute(): string
    {
        return preg_replace('/[^0-9]/', '', $this->cpf);
    }
}
