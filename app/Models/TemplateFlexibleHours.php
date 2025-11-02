<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateFlexibleHours extends Model
{
    protected $fillable = [
        'template_id',
        'weekly_hours_required',
        'period_type',
        'grace_minutes',
        'requires_minimum_daily_hours',
        'minimum_daily_hours',
        'minimum_days_per_week',
    ];

    protected $casts = [
        'weekly_hours_required' => 'decimal:2',
        'grace_minutes' => 'integer',
        'requires_minimum_daily_hours' => 'boolean',
        'minimum_daily_hours' => 'decimal:2',
        'minimum_days_per_week' => 'integer',
    ];

    /**
     * Relacionamento com o template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkShiftTemplate::class, 'template_id');
    }

    /**
     * Retorna o tipo de período formatado
     */
    public function getPeriodTypeFormattedAttribute(): string
    {
        return match($this->period_type) {
            'weekly' => 'Semanal',
            'biweekly' => 'Quinzenal',
            'monthly' => 'Mensal',
            default => $this->period_type,
        };
    }
}
