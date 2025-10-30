<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2; // +2 porque linha 1 é cabeçalho e array começa em 0
            
            $validator = Validator::make($row, [
                'nome_completo' => 'required|string|max:255',
                'cpf' => 'required|string|size:11|regex:/^\d{11}$/',
                'pis_pasep' => 'required|string|size:11|regex:/^\d{11}$/',
                'email' => 'nullable|email|max:255',
                'telefone' => 'nullable|string|max:20',
                'estabelecimento' => 'required|string|max:255',
                'departamento' => 'required|string|max:255',
                'cargo' => 'nullable|string|max:255',
                'data_admissao' => 'required|date_format:Y-m-d',
                'salario' => 'nullable|numeric|min:0',
                'status' => 'required|in:ativo,inativo',
            ], [
                'nome_completo.required' => 'O nome completo é obrigatório',
                'cpf.required' => 'O CPF é obrigatório',
                'cpf.size' => 'O CPF deve ter 11 dígitos',
                'cpf.regex' => 'O CPF deve conter apenas números',
                'pis_pasep.required' => 'O PIS/PASEP é obrigatório',
                'pis_pasep.size' => 'O PIS/PASEP deve ter 11 dígitos',
                'pis_pasep.regex' => 'O PIS/PASEP deve conter apenas números',
                'email.email' => 'O email deve ser válido',
                'estabelecimento.required' => 'O estabelecimento é obrigatório',
                'departamento.required' => 'O departamento é obrigatório',
                'data_admissao.required' => 'A data de admissão é obrigatória',
                'data_admissao.date_format' => 'A data de admissão deve estar no formato YYYY-MM-DD',
                'status.required' => 'O status é obrigatório',
                'status.in' => 'O status deve ser "ativo" ou "inativo"',
            ]);

            if ($validator->fails()) {
                $this->invalidRows[] = [
                    'line' => $lineNumber,
                    'data' => $row,
                    'errors' => $validator->errors()->all(),
                ];
                $this->errors[] = "Linha {$lineNumber}: " . implode(', ', $validator->errors()->all());
            } else {
                // Validar CPF
                if (!$this->validarCPF($row['cpf'])) {
                    $this->invalidRows[] = [
                        'line' => $lineNumber,
                        'data' => $row,
                        'errors' => ['CPF inválido'],
                    ];
                    $this->errors[] = "Linha {$lineNumber}: CPF inválido";
                } else {
                    $this->validRows[] = [
                        'line' => $lineNumber,
                        'data' => $row,
                    ];
                }
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
