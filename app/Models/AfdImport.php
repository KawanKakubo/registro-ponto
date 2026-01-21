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
        'format_type',
        'format_hint',
        'cnpj',
        'start_date',
        'end_date',
        'total_records',
        'records_imported',
        'status',
        'error_message',
        'pending_employees',
        'pending_records',
        'pending_count',
        'processed_at',
        'imported_by',
        'imported_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'imported_at' => 'datetime',
        'processed_at' => 'datetime',
        'pending_employees' => 'array',
        'pending_records' => 'array',
    ];

    /**
     * Relacionamento com usuário que importou
     */
    public function importedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Alias para o relacionamento com usuário (para compatibilidade)
     */
    public function user(): BelongsTo
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

    /**
     * Verifica se há colaboradores pendentes de revisão
     */
    public function hasPendingEmployees(): bool
    {
        return $this->pending_count > 0 && !empty($this->pending_employees);
    }

    /**
     * Verifica se a importação precisa de revisão
     */
    public function needsReview(): bool
    {
        return $this->status === 'pending_review' || $this->hasPendingEmployees();
    }

    /**
     * Retorna os colaboradores pendentes como coleção
     */
    public function getPendingEmployeesCollection(): \Illuminate\Support\Collection
    {
        return collect($this->pending_employees ?? []);
    }

    /**
     * Retorna os registros pendentes como coleção
     */
    public function getPendingRecordsCollection(): \Illuminate\Support\Collection
    {
        return collect($this->pending_records ?? []);
    }

    /**
     * Remove um colaborador da lista de pendentes (após cadastro ou skip)
     */
    public function removePendingEmployee(string $employeeKey): void
    {
        $pendingEmployees = $this->pending_employees ?? [];
        $pendingRecords = $this->pending_records ?? [];

        // Remove o colaborador
        $pendingEmployees = array_filter($pendingEmployees, fn($emp) => $emp['key'] !== $employeeKey);
        
        // Remove os registros associados a esse colaborador
        $pendingRecords = array_filter($pendingRecords, fn($rec) => $rec['employee_key'] !== $employeeKey);

        $this->update([
            'pending_employees' => array_values($pendingEmployees),
            'pending_records' => array_values($pendingRecords),
            'pending_count' => count($pendingEmployees),
        ]);

        // Se não há mais pendentes, marcar como concluído
        if (empty($pendingEmployees)) {
            $this->update(['status' => 'completed']);
        }
    }

    /**
     * Busca os registros pendentes de um colaborador específico
     */
    public function getPendingRecordsFor(string $employeeKey): array
    {
        return array_filter($this->pending_records ?? [], fn($rec) => $rec['employee_key'] === $employeeKey);
    }
}
