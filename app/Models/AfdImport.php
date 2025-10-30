<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfdImport extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'file_size',
        'cnpj',
        'start_date',
        'end_date',
        'total_records',
        'records_imported',
        'status',
        'error_message',
        'processed_at',
        'imported_by',
        'imported_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'imported_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário que importou
     */
    public function importedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Verifica se a importação foi bem-sucedida
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
}
