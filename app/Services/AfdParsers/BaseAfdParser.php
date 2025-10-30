<?php

namespace App\Services\AfdParsers;

use App\Models\Employee;
use App\Models\TimeRecord;
use App\Models\AfdImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Classe base abstrata para parsers de AFD
 * 
 * Contém lógica comum a todos os parsers
 */
abstract class BaseAfdParser implements AfdParserInterface
{
    protected AfdImport $afdImport;
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $skippedCount = 0;

    /**
     * Processa o arquivo AFD
     */
    public function parse(string $filePath, AfdImport $afdImport): array
    {
        $this->afdImport = $afdImport;
        $this->errors = [];
        $this->successCount = 0;
        $this->skippedCount = 0;

        try {
            // Converter para caminho absoluto se necessário
            $fullPath = str_starts_with($filePath, '/') ? $filePath : storage_path('app/' . $filePath);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("Arquivo não encontrado: {$fullPath}");
            }
            
            Log::info("AFD Parser ({$this->getFormatName()}): Processando arquivo {$fullPath}");

            // Registra o formato detectado
            $this->afdImport->update([
                'format_type' => $this->getFormatName(),
            ]);

            DB::beginTransaction();

            $this->processFile($fullPath);

            $this->afdImport->update([
                'status' => 'completed',
                'total_records' => $this->successCount,
            ]);

            DB::commit();

            return [
                'success' => true,
                'imported' => $this->successCount,
                'skipped' => $this->skippedCount,
                'errors' => $this->errors,
                'format' => $this->getFormatName(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->afdImport->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Erro ao processar AFD: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'imported' => $this->successCount,
                'skipped' => $this->skippedCount,
                'errors' => $this->errors,
            ];
        }
    }

    /**
     * Método abstrato para processar o arquivo - cada parser implementa sua lógica
     */
    abstract protected function processFile(string $filePath): void;

    /**
     * Busca um colaborador por PIS, Matrícula ou CPF
     * 
     * @param string|null $pis PIS/PASEP
     * @param string|null $matricula Matrícula
     * @param string|null $cpf CPF
     * @return Employee|null
     */
    protected function findEmployee(?string $pis = null, ?string $matricula = null, ?string $cpf = null): ?Employee
    {
        $query = Employee::query();

        // Prioridade: PIS > Matrícula > CPF
        if ($pis) {
            $normalizedPis = preg_replace('/[^0-9]/', '', $pis);
            $employee = $query->where('pis_pasep', $normalizedPis)->first();
            if ($employee) {
                return $employee;
            }
        }

        if ($matricula) {
            $normalizedMatricula = preg_replace('/[^0-9A-Za-z]/', '', $matricula);
            $employee = Employee::where('matricula', $normalizedMatricula)->first();
            if ($employee) {
                return $employee;
            }
        }

        if ($cpf) {
            $normalizedCpf = preg_replace('/[^0-9]/', '', $cpf);
            $employee = Employee::where('cpf', $normalizedCpf)->first();
            if ($employee) {
                return $employee;
            }
        }

        return null;
    }

    /**
     * Cria um registro de ponto
     */
    protected function createTimeRecord(
        Employee $employee, 
        Carbon $recordedAt, 
        string $nsr, 
        string $recordType
    ): bool {
        // Verifica se já existe
        $exists = TimeRecord::where('employee_id', $employee->id)
            ->where('recorded_at', $recordedAt)
            ->exists();

        if ($exists) {
            $this->skippedCount++;
            return false;
        }

        TimeRecord::create([
            'employee_id' => $employee->id,
            'recorded_at' => $recordedAt,
            'record_date' => $recordedAt->format('Y-m-d'),
            'record_time' => $recordedAt->format('H:i:s'),
            'nsr' => $nsr,
            'record_type' => $recordType,
            'imported_from_afd' => true,
            'afd_file_name' => $this->afdImport->file_name,
        ]);

        $this->successCount++;
        return true;
    }

    /**
     * Parse de data/hora - múltiplos formatos
     */
    protected function parseDateTime(string $dateTimeStr): ?Carbon
    {
        try {
            // Tenta formato padrão ISO
            return Carbon::parse($dateTimeStr);
        } catch (\Exception $e) {
            // Tenta outros formatos comuns
            $formats = [
                'dmYHis',     // 28042014134621
                'd/m/Y H:i:s',
                'Y-m-d H:i:s',
                'Ymd His',
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateTimeStr);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return null;
        }
    }

    /**
     * Normaliza CPF removendo formatação
     */
    protected function normalizeCpf(string $cpfRaw): ?string
    {
        // Remove pontos, traços e espaços
        $cpf = preg_replace('/[^0-9]/', '', trim($cpfRaw));
        
        // CPF deve ter exatamente 11 dígitos
        if (strlen($cpf) != 11) {
            return null;
        }

        return $cpf;
    }

    /**
     * Normaliza PIS/PASEP removendo formatação
     */
    protected function normalizePis(string $pisRaw): ?string
    {
        $pis = preg_replace('/[^0-9]/', '', trim($pisRaw));
        
        if (strlen($pis) != 11) {
            return null;
        }

        return $pis;
    }

    /**
     * Atualiza o header da importação
     */
    protected function updateImportHeader(array $headerData): void
    {
        $this->afdImport->update([
            'cnpj' => $headerData['cnpj_cpf_employer'] ?? null,
            'start_date' => $headerData['start_date'] ?? null,
            'end_date' => $headerData['end_date'] ?? null,
        ]);
    }

    /**
     * Adiciona um erro à lista
     */
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
        Log::warning("AFD Parser Error: {$message}");
    }
}
