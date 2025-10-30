<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateRotatingRule extends Model
{
    protected $fillable = [
        'template_id',
        'work_days',
        'rest_days',
        'shift_start_time',
        'shift_end_time',
        'shift_duration_hours',
    ];

    protected $casts = [
        'shift_start_time' => 'datetime:H:i:s',
        'shift_end_time' => 'datetime:H:i:s',
        'shift_duration_hours' => 'decimal:2',
    ];

    /**
     * Relacionamento com o template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkShiftTemplate::class, 'template_id');
    }

    /**
     * Retorna o tamanho total do ciclo (dias de trabalho + dias de descanso)
     */
    public function getCycleLengthAttribute(): int
    {
        return $this->work_days + $this->rest_days;
    }

    /**
     * Retorna uma descrição formatada da escala (ex: "12x36", "6x1")
     */
    public function getScaleDescriptionAttribute(): string
    {
        // Para escala 12x36, o padrão é 1 dia de trabalho (12h) e 1 dia de descanso
        if ($this->work_days == 1 && $this->rest_days == 1 && $this->shift_duration_hours == 12) {
            return '12x36';
        }
        
        // Para outras escalas, usa o formato padrão
        return "{$this->work_days}x{$this->rest_days}";
    }

    /**
     * Calcula se uma data específica é dia de trabalho baseado na data de início do ciclo
     */
    public function isWorkingDay(\DateTime $targetDate, \DateTime $cycleStartDate): bool
    {
        $daysSinceStart = $targetDate->diff($cycleStartDate)->days;
        $positionInCycle = $daysSinceStart % $this->cycle_length;
        
        return $positionInCycle < $this->work_days;
    }

    /**
     * Calcula e atualiza automaticamente a duração do turno
     */
    public function calculateShiftDuration(): float
    {
        if (!$this->shift_start_time || !$this->shift_end_time) {
            return 0;
        }

        $startTime = is_string($this->shift_start_time) ? $this->shift_start_time : $this->shift_start_time->format('H:i:s');
        $endTime = is_string($this->shift_end_time) ? $this->shift_end_time : $this->shift_end_time->format('H:i:s');

        $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);

        // Se o fim é antes do início, o turno cruza a meia-noite
        if ($end->lt($start)) {
            $end->addDay();
        }

        return round($start->diffInMinutes($end, false) / 60, 2);
    }

    /**
     * Atualiza automaticamente o campo shift_duration_hours antes de salvar
     */
    protected static function booted()
    {
        static::saving(function ($rule) {
            if ($rule->shift_start_time && $rule->shift_end_time) {
                $rule->shift_duration_hours = $rule->calculateShiftDuration();
            }
        });
    }
}
