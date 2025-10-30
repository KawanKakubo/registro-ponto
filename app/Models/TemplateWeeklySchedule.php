<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateWeeklySchedule extends Model
{
    protected $fillable = [
        'template_id',
        'day_of_week',
        'entry_1',
        'exit_1',
        'entry_2',
        'exit_2',
        'entry_3',
        'exit_3',
        'is_work_day',
        'daily_hours',
    ];

    protected $casts = [
        'is_work_day' => 'boolean',
        'daily_hours' => 'decimal:2',
    ];

    /**
     * Relacionamento com o template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkShiftTemplate::class, 'template_id');
    }

    /**
     * Retorna o nome do dia da semana em português
     */
    public function getDayNameAttribute(): string
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return $days[$this->day_of_week] ?? '';
    }

    /**
     * Retorna o nome curto do dia da semana
     */
    public function getDayShortNameAttribute(): string
    {
        $days = [
            0 => 'Dom',
            1 => 'Seg',
            2 => 'Ter',
            3 => 'Qua',
            4 => 'Qui',
            5 => 'Sex',
            6 => 'Sáb',
        ];

        return $days[$this->day_of_week] ?? '';
    }

    /**
     * Calcula e retorna as horas diárias trabalhadas
     */
    public function calculateDailyHours(): float
    {
        if (!$this->is_work_day) {
            return 0;
        }

        $totalMinutes = 0;

        // Período 1
        if ($this->entry_1 && $this->exit_1) {
            // Pega apenas a parte do tempo, ignorando a data
            $startTime = is_string($this->entry_1) ? $this->entry_1 : $this->entry_1->format('H:i:s');
            $endTime = is_string($this->exit_1) ? $this->exit_1 : $this->exit_1->format('H:i:s');
            
            $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
            $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
            $totalMinutes += $start->diffInMinutes($end, false);
        }

        // Período 2
        if ($this->entry_2 && $this->exit_2) {
            $startTime = is_string($this->entry_2) ? $this->entry_2 : $this->entry_2->format('H:i:s');
            $endTime = is_string($this->exit_2) ? $this->exit_2 : $this->exit_2->format('H:i:s');
            
            $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
            $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
            $totalMinutes += $start->diffInMinutes($end, false);
        }

        // Período 3
        if ($this->entry_3 && $this->exit_3) {
            $startTime = is_string($this->entry_3) ? $this->entry_3 : $this->entry_3->format('H:i:s');
            $endTime = is_string($this->exit_3) ? $this->exit_3 : $this->exit_3->format('H:i:s');
            
            $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
            $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
            $totalMinutes += $start->diffInMinutes($end, false);
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Atualiza automaticamente o campo daily_hours antes de salvar
     */
    protected static function booted()
    {
        static::saving(function ($schedule) {
            $schedule->daily_hours = $schedule->calculateDailyHours();
        });
    }
}
