<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkShiftAssignment extends Model
{
    protected $fillable = [
        'employee_id',
        'template_id',
        'cycle_start_date',
        'effective_from',
        'effective_until',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'cycle_start_date' => 'date',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'assigned_at' => 'datetime',
    ];

    /**
     * Relacionamento com o colaborador
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relacionamento com o template de jornada
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkShiftTemplate::class, 'template_id');
    }

    /**
     * Relacionamento com o usuário que fez a atribuição
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope para buscar atribuições ativas (vigentes atualmente)
     */
    public function scopeActive($query)
    {
        return $query->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            });
    }

    /**
     * Scope para buscar atribuições futuras
     */
    public function scopeFuture($query)
    {
        return $query->where('effective_from', '>', now());
    }

    /**
     * Scope para buscar atribuições expiradas
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('effective_until')
            ->where('effective_until', '<', now());
    }

    /**
     * Verifica se a atribuição está ativa
     */
    public function isActive(): bool
    {
        $now = now();
        
        return $this->effective_from <= $now 
            && ($this->effective_until === null || $this->effective_until >= $now);
    }

    /**
     * Verifica se a atribuição é de um template de escala rotativa
     */
    public function isRotatingShift(): bool
    {
        return $this->template->isRotatingShift();
    }

    /**
     * Verifica se uma data específica é dia de trabalho para este colaborador
     * (só funciona para escalas rotativas)
     */
    public function isWorkingDay(\DateTime $date): bool
    {
        if (!$this->isRotatingShift() || !$this->cycle_start_date) {
            return false;
        }

        $rule = $this->template->rotatingRule;
        if (!$rule) {
            return false;
        }

        return $rule->isWorkingDay($date, new \DateTime($this->cycle_start_date->format('Y-m-d')));
    }

    /**
     * Retorna o status formatado da atribuição
     */
    public function getStatusAttribute(): string
    {
        if ($this->isActive()) {
            return 'Ativa';
        }
        
        if ($this->effective_from > now()) {
            return 'Futura';
        }
        
        return 'Expirada';
    }
}
