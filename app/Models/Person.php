<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $table = 'people';

    protected $fillable = [
        'full_name',
        'cpf',
        'pis_pasep',
        'ctps',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento: Uma Pessoa pode ter N Vínculos (Matrículas)
     */
    public function employeeRegistrations(): HasMany
    {
        return $this->hasMany(EmployeeRegistration::class);
    }

    /**
     * Relacionamento: Vínculos ativos
     */
    public function activeRegistrations(): HasMany
    {
        return $this->employeeRegistrations()->where('status', 'active');
    }

    /**
     * Mutator para limpar CPF ao salvar (remove formatação)
     */
    public function setCpfAttribute($value): void
    {
        // Se valor for vazio, null, ou false, manter como null
        if (empty($value)) {
            $this->attributes['cpf'] = null;
            return;
        }
        
        // Limpar formatação do CPF
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        
        // Se após limpar ficar vazio, salvar como null
        $this->attributes['cpf'] = empty($cleaned) ? null : $cleaned;
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
        $this->attributes['pis_pasep'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Accessor para obter CPF formatado
     */
    public function getCpfFormattedAttribute(): string
    {
        $cpf = $this->cpf;
        
        // Se CPF for null ou vazio, retornar string vazia
        if (empty($cpf)) {
            return '';
        }
        
        // Se tiver 11 dígitos, formatar
        if (strlen($cpf) == 11) {
            return sprintf(
                '%s.%s.%s-%s',
                substr($cpf, 0, 3),
                substr($cpf, 3, 3),
                substr($cpf, 6, 3),
                substr($cpf, 9, 2)
            );
        }
        
        // Retornar sem formatação se não tiver 11 dígitos
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
     * Verifica se a pessoa tem vínculos ativos
     */
    public function hasActiveRegistrations(): bool
    {
        return $this->activeRegistrations()->exists();
    }
}
