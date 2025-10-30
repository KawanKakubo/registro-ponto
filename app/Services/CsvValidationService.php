<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use App\Models\Establishment;
use App\Models\Department;
use App\Models\Employee;

class CsvValidationService
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $validRows = [];
    protected array $invalidRows = [];

    public function validate(array $rows): array
    {
        $this->errors = [];
        $this->warnings = [];
        $this->validRows = [];
        $this->invalidRows = [];

        // Cache dos IDs válidos
        $validEstablishments = Establishment::pluck('id')->toArray();
        $validDepartments = Department::pluck('id')->toArray();
        $existingCpfs = Employee::pluck('cpf')->toArray();
        $existingPis = Employee::whereNotNull('pis_pasep')->pluck('pis_pasep')->toArray();

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2; // +2 porque linha 1 é cabeçalho e array começa em 0
            $lineErrors = [];
            
            // Validar campos obrigatórios
            $validator = Validator::make($row, [
                'cpf' => 'required|string',
                'full_name' => 'required|string|max:255',
                'pis_pasep' => 'required|string',
                'establishment_id' => 'required|integer',
                'department_id' => 'nullable|integer',
                'admission_date' => 'required|date_format:Y-m-d',
                'role' => 'nullable|string|max:255',
            ], [
                'cpf.required' => 'CPF é obrigatório',
                'full_name.required' => 'Nome completo é obrigatório',
                'pis_pasep.required' => 'PIS/PASEP é obrigatório',
                'establishment_id.required' => 'ID do estabelecimento é obrigatório',
                'establishment_id.integer' => 'ID do estabelecimento deve ser um número',
                'department_id.integer' => 'ID do departamento deve ser um número',
                'admission_date.required' => 'Data de admissão é obrigatória',
                'admission_date.date_format' => 'Data de admissão deve estar no formato YYYY-MM-DD',
            ]);

            if ($validator->fails()) {
                $lineErrors = array_merge($lineErrors, $validator->errors()->all());
            } else {
                // Limpar e validar CPF
                $cpf = preg_replace('/[^0-9]/', '', $row['cpf']);
                if (strlen($cpf) != 11) {
                    $lineErrors[] = 'CPF deve ter 11 dígitos';
                } elseif (!$this->validarCPF($cpf)) {
                    $lineErrors[] = 'CPF inválido';
                } elseif (in_array($cpf, $existingCpfs)) {
                    $this->warnings[] = "Linha {$lineNumber}: CPF {$cpf} já existe no sistema (será atualizado)";
                }

                // Limpar e validar PIS/PASEP
                $pis = preg_replace('/[^0-9]/', '', $row['pis_pasep']);
                if (strlen($pis) != 11) {
                    $lineErrors[] = 'PIS/PASEP deve ter 11 dígitos';
                } elseif (in_array($pis, $existingPis)) {
                    $this->warnings[] = "Linha {$lineNumber}: PIS/PASEP {$pis} já existe no sistema (será atualizado)";
                }

                // Validar establishment_id
                if (!in_array((int)$row['establishment_id'], $validEstablishments)) {
                    $lineErrors[] = "Estabelecimento ID {$row['establishment_id']} não existe";
                }

                // Validar department_id (se fornecido)
                if (!empty($row['department_id']) && !in_array((int)$row['department_id'], $validDepartments)) {
                    $lineErrors[] = "Departamento ID {$row['department_id']} não existe";
                }
            }

            if (count($lineErrors) > 0) {
                $this->invalidRows[] = [
                    'line' => $lineNumber,
                    'data' => $row,
                    'errors' => $lineErrors,
                ];
                $this->errors[] = "Linha {$lineNumber}: " . implode(', ', $lineErrors);
            } else {
                $this->validRows[] = [
                    'line' => $lineNumber,
                    'data' => $row,
                ];
            }
        }

        return [
            'valid' => count($this->errors) === 0,
            'total_rows' => count($rows),
            'valid_rows' => count($this->validRows),
            'invalid_rows' => count($this->invalidRows),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'valid_data' => $this->validRows,
            'invalid_data' => $this->invalidRows,
        ];
    }

    protected function validarCPF(string $cpf): bool
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Valida primeiro dígito verificador
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getValidRows(): array
    {
        return $this->validRows;
    }

    public function getInvalidRows(): array
    {
        return $this->invalidRows;
    }
}
