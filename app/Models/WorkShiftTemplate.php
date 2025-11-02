<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkShiftTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'is_preset',
        'weekly_hours',
        'created_by',
    ];

    protected $casts = [
        'is_preset' => 'boolean',
        'weekly_hours' => 'decimal:2',
    ];

    /**
     * Relacionamento com horários semanais (para templates tipo 'weekly')
     */
    public function weeklySchedules(): HasMany
    {
        return $this->hasMany(TemplateWeeklySchedule::class, 'template_id');
    }

    /**
     * Relacionamento com regras de escala rotativa (para templates tipo 'rotating_shift')
     */
    public function rotatingRule(): HasOne
    {
        return $this->hasOne(TemplateRotatingRule::class, 'template_id');
    }

    /**
     * Relacionamento com configuração de carga horária flexível (para templates tipo 'weekly_hours')
     */
    public function flexibleHours(): HasOne
    {
        return $this->hasOne(TemplateFlexibleHours::class, 'template_id');
    }

    /**
     * Relacionamento com atribuições de colaboradores
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeWorkShiftAssignment::class, 'template_id');
    }

    /**
     * Relacionamento many-to-many com colaboradores através das atribuições
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_work_shift_assignments', 'template_id', 'employee_id')
            ->withPivot(['cycle_start_date', 'effective_from', 'effective_until', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Relacionamento com o usuário criador
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para buscar apenas templates semanais
     */
    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    /**
     * Scope para buscar apenas templates de escala rotativa
     */
    public function scopeRotatingShift($query)
    {
        return $query->where('type', 'rotating_shift');
    }

    /**
     * Scope para buscar apenas presets
     */
    public function scopePresets($query)
    {
        return $query->where('is_preset', true);
    }

    /**
     * Scope para buscar templates customizados (não-presets)
     */
    public function scopeCustom($query)
    {
        return $query->where('is_preset', false);
    }

    /**
     * Verifica se o template é semanal
     */
    public function isWeekly(): bool
    {
        return $this->type === 'weekly';
    }

    /**
     * Verifica se o template é de escala rotativa
     */
    public function isRotatingShift(): bool
    {
        return $this->type === 'rotating_shift';
    }

    /**
     * Verifica se o template é de carga horária flexível
     */
    public function isWeeklyHours(): bool
    {
        return $this->type === 'weekly_hours';
    }

    /**
     * Conta quantos colaboradores estão usando este template atualmente
     */
    public function getCurrentEmployeesCount(): int
    {
        return $this->assignments()
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->count();
    }

    /**
     * Retorna o tipo formatado para exibição
     */
    public function getTypeFormattedAttribute(): string
    {
        return match($this->type) {
            'weekly' => 'Semanal Fixa',
            'rotating_shift' => 'Escala Rotativa',
            'weekly_hours' => 'Carga Horária',
            default => $this->type,
        };
    }

    /**
     * Scope para buscar apenas templates de carga horária
     */
    public function scopeWeeklyHours($query)
    {
        return $query->where('type', 'weekly_hours');
    }
}
