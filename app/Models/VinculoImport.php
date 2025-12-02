<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VinculoImport extends Model
{
    protected $fillable = [
        'filename',
        'csv_path',
        'user_id',
        'total_linhas',
        'pessoas_criadas',
        'pessoas_atualizadas',
        'vinculos_criados',
        'vinculos_atualizados',
        'jornadas_associadas',
        'erros',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário que iniciou a importação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se a importação está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se a importação está em processamento
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Verifica se a importação foi concluída
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verifica se a importação falhou
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Calcula a taxa de sucesso
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_linhas == 0) {
            return 0;
        }

        $sucessos = $this->total_linhas - $this->erros;
        return ($sucessos / $this->total_linhas) * 100;
    }

    /**
     * Retorna o status formatado
     */
    public function getStatusFormattedAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'completed' => 'Concluída',
            'failed' => 'Falhou',
            default => $this->status,
        };
    }
}
