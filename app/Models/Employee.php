<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'establishment_id',
        'department_id',
        'full_name',
        'cpf',
        'pis_pasep',
        'matricula',
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
     * Relacionamento com atribuições de jornada
     */
    public function workShiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeWorkShiftAssignment::class);
    }

    /**
     * Retorna a atribuição de jornada atual (ativa)
     */
    public function currentWorkShiftAssignment(): HasOne
    {
        return $this->hasOne(EmployeeWorkShiftAssignment::class)
            ->active()
            ->latest('effective_from');
    }

    /**
     * Verifica se o colaborador tem uma jornada atribuída
     */
    public function hasWorkShift(): bool
    {
        return $this->currentWorkShiftAssignment()->exists();
    }

    /**
     * Mutator para limpar CPF ao salvar (remove formatação)
     */
    public function setCpfAttribute($value): void
    {
        // Remove tudo que não é número e salva limpo
        $this->attributes['cpf'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Mutator para limpar PIS ao salvar (remove formatação)
     */
    public function setPisPasepAttribute($value): void
    {
        if (!$value) {
            $this->attributes['pis_pasep'] = null;
            return;
        }

        // Remove tudo que não é número e salva limpo
        $this->attributes['pis_pasep'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Mutator para limpar Matrícula ao salvar (remove formatação)
     */
    public function setMatriculaAttribute($value): void
    {
        if (!$value) {
            $this->attributes['matricula'] = null;
            return;
        }

        // Remove espaços e caracteres especiais
        $this->attributes['matricula'] = preg_replace('/[^0-9A-Za-z]/', '', $value);
    }
    
    /**
     * Accessor para obter CPF formatado
     */
    public function getCpfFormattedAttribute(): string
    {
        $cpf = $this->cpf;
        if (strlen($cpf) == 11) {
            return sprintf(
                '%s.%s.%s-%s',
                substr($cpf, 0, 3),
                substr($cpf, 3, 3),
                substr($cpf, 6, 3),
                substr($cpf, 9, 2)
            );
        }
        return $cpf;
    }
    
    /**
     * Accessor para obter PIS formatado
     */
    public function getPisPasepFormattedAttribute(): ?string
    {
        if (!$this->pis_pasep) {
            return null;
        }
        
        $pis = $this->pis_pasep;
        if (strlen($pis) == 11) {
            return sprintf(
                '%s.%s.%s-%s',
                substr($pis, 0, 3),
                substr($pis, 3, 5),
                substr($pis, 8, 2),
                substr($pis, 10, 1)
            );
        }
        return $pis;
    }

    /**
     * Scope para colaboradores ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
