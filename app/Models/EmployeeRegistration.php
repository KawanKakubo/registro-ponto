<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmployeeRegistration extends Model
{
    protected $fillable = [
        'person_id',
        'matricula',
        'establishment_id',
        'department_id',
        'admission_date',
        'position',
        'status',
    ];

    protected $casts = [
        'admission_date' => 'date',
    ];

    /**
     * Relacionamento: Vínculo pertence a uma Pessoa
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Relacionamento: Vínculo pertence a um Estabelecimento
     */
    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    /**
     * Relacionamento: Vínculo pertence a um Departamento
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relacionamento: Registros de ponto do vínculo
     */
    public function timeRecords(): HasMany
    {
        return $this->hasMany(TimeRecord::class);
    }

    /**
     * Relacionamento: Horários de trabalho do vínculo
     */
    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    /**
     * Relacionamento: Atribuições de jornada do vínculo
     */
    public function workShiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeWorkShiftAssignment::class);
    }

    /**
     * Relacionamento: Atribuição de jornada atual (ativa)
     */
    public function currentWorkShiftAssignment(): HasOne
    {
        return $this->hasOne(EmployeeWorkShiftAssignment::class)
            ->active()
            ->latest('effective_from');
    }

    /**
     * Verifica se o vínculo tem uma jornada atribuída
     */
    public function hasWorkShift(): bool
    {
        return $this->currentWorkShiftAssignment()->exists();
    }

    /**
     * Mutator para limpar Matrícula ao salvar
     */
    public function setMatriculaAttribute($value): void
    {
        if (!$value) {
            $this->attributes['matricula'] = null;
            return;
        }
        $this->attributes['matricula'] = preg_replace('/[^0-9A-Za-z]/', '', $value);
    }

    /**
     * Scope para vínculos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para vínculos de um estabelecimento
     */
    public function scopeFromEstablishment($query, $establishmentId)
    {
        return $query->where('establishment_id', $establishmentId);
    }

    /**
     * Scope para vínculos de um departamento
     */
    public function scopeFromDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
