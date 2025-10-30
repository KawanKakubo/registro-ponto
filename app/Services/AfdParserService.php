<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimeRecord;
use App\Models\AfdImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AfdParserService
{
    protected $afdImport;
    protected $errors = [];
    protected $successCount = 0;
    protected $skippedCount = 0;

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
                throw new \Exception("Arquivo não encontrado: {$fullPath} (path original: {$filePath})");
            }
            
            Log::info("AFD Parser: Arquivo encontrado em {$fullPath}");
            
            // Usar o caminho completo daqui em diante
            $filePath = $fullPath;

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Não foi possível abrir o arquivo");
            }

            $lineNumber = 0;
            $headerData = null;

            DB::beginTransaction();

            while (($line = fgets($handle)) !== false) {
                $lineNumber++;
                $line = trim($line);

                if (empty($line)) {
                    continue;
                }

                $recordType = substr($line, 9, 1);

                switch ($recordType) {
                    case '1':
                        $headerData = $this->parseHeaderRecord($line);
                        $this->updateImportHeader($headerData);
                        break;
                    
                    case '2':
                        $this->parseCompanyRecord($line);
                        break;
                    
                    case '3':
                        $this->parseTimeRecord($line, $lineNumber);
                        break;
                    
                    case '7':
                        $this->parseTimeRecordRepP($line, $lineNumber);
                        break;
                    
                    case '4':
                    case '5':
                    case '6':
                        Log::info("AFD: Registro tipo {$recordType} na linha {$lineNumber}");
                        break;
                    
                    default:
                        Log::warning("AFD: Tipo de registro desconhecido '{$recordType}' na linha {$lineNumber}");
                        break;
                }
            }

            fclose($handle);

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

    protected function parseHeaderRecord(string $line): array
    {
        return [
            'nsr' => trim(substr($line, 0, 9)),
            'record_type' => substr($line, 9, 1),
            'cnpj_cpf_employer' => trim(substr($line, 10, 14)),
            'cno_caepf' => trim(substr($line, 25, 14)),
            'corporate_name' => trim(substr($line, 39, 150)),
            'rep_serial' => trim(substr($line, 189, 17)),
            'start_date' => $this->parseDateTime(substr($line, 206, 24)),
            'end_date' => $this->parseDateTime(substr($line, 230, 24)),
            'generation_date' => $this->parseDateTime(substr($line, 254, 24)),
        ];
    }

    protected function parseCompanyRecord(string $line): void
    {
        $cnpj = trim(substr($line, 11, 14));
        Log::info("AFD: Registro de empresa CNPJ: {$cnpj}");
    }

    protected function parseTimeRecord(string $line, int $lineNumber): void
    {
        try {
            $nsr = trim(substr($line, 0, 9));
            $dateTimeStr = substr($line, 10, 24);
            $recordedAt = $this->parseDateTime($dateTimeStr);
            
            if (!$recordedAt) {
                $this->errors[] = "Linha {$lineNumber}: Data/hora inválida";
                $this->skippedCount++;
                return;
            }

            $cpfRaw = trim(substr($line, 34, 12));
            $cpf = $this->normalizeCpf($cpfRaw);
            
            if (!$cpf) {
                $this->errors[] = "Linha {$lineNumber}: CPF inválido '{$cpfRaw}'";
                $this->skippedCount++;
                return;
            }

            $employee = Employee::where('cpf', $cpf)->first();
            
            if (!$employee) {
                $this->errors[] = "Linha {$lineNumber}: Colaborador com CPF {$cpf} não encontrado";
                $this->skippedCount++;
                return;
            }

            $exists = TimeRecord::where('employee_id', $employee->id)
                ->where('recorded_at', $recordedAt)
                ->exists();

            if ($exists) {
                $this->skippedCount++;
                return;
            }

            TimeRecord::create([
                'employee_id' => $employee->id,
                'recorded_at' => $recordedAt,
                'record_date' => $recordedAt->format('Y-m-d'),
                'record_time' => $recordedAt->format('H:i:s'),
                'nsr' => $nsr,
                'record_type' => '3',
                'imported_from_afd' => true,
                'afd_file_name' => $this->afdImport->file_name,
            ]);

            $this->successCount++;

        } catch (\Exception $e) {
            $this->errors[] = "Linha {$lineNumber}: " . $e->getMessage();
            $this->skippedCount++;
            Log::error("Erro ao processar linha {$lineNumber}: " . $e->getMessage());
        }
    }

    protected function parseTimeRecordRepP(string $line, int $lineNumber): void
    {
        try {
            $nsr = trim(substr($line, 0, 9));
            $dateTimeStr = substr($line, 10, 24);
            $recordedAt = $this->parseDateTime($dateTimeStr);
            
            if (!$recordedAt) {
                $this->errors[] = "Linha {$lineNumber}: Data/hora inválida";
                $this->skippedCount++;
                return;
            }

            $cpfRaw = trim(substr($line, 34, 12));
            $cpf = $this->normalizeCpf($cpfRaw);
            
            if (!$cpf) {
                $this->errors[] = "Linha {$lineNumber}: CPF inválido";
                $this->skippedCount++;
                return;
            }

            $employee = Employee::where('cpf', $cpf)->first();
            
            if (!$employee) {
                $this->errors[] = "Linha {$lineNumber}: Colaborador com CPF {$cpf} não encontrado";
                $this->skippedCount++;
                return;
            }

            $exists = TimeRecord::where('employee_id', $employee->id)
                ->where('recorded_at', $recordedAt)
                ->exists();

            if ($exists) {
                $this->skippedCount++;
                return;
            }

            TimeRecord::create([
                'employee_id' => $employee->id,
                'recorded_at' => $recordedAt,
                'record_date' => $recordedAt->format('Y-m-d'),
                'record_time' => $recordedAt->format('H:i:s'),
                'nsr' => $nsr,
                'record_type' => '7',
                'imported_from_afd' => true,
                'afd_file_name' => $this->afdImport->file_name,
            ]);

            $this->successCount++;

        } catch (\Exception $e) {
            $this->errors[] = "Linha {$lineNumber}: " . $e->getMessage();
            $this->skippedCount++;
        }
    }

    protected function parseDateTime(string $dateTimeStr): ?Carbon
    {
        try {
            return Carbon::parse($dateTimeStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function normalizeCpf(string $cpfRaw): ?string
    {
        $cpfRaw = trim($cpfRaw);
        $firstChar = substr($cpfRaw, 0, 1);
        
        if ($firstChar === '9') {
            $cpf = substr($cpfRaw, 1, 11);
        } elseif ($firstChar === '8') {
            $cpf = substr($cpfRaw, 1, 10);
            $cpf .= substr($cpfRaw, 11, 1);
        } else {
            if (strlen($cpfRaw) == 11) {
                $cpf = $cpfRaw;
            } elseif (strlen($cpfRaw) == 12) {
                $cpf = substr($cpfRaw, 1, 11);
            } else {
                return null;
            }
        }

        $cpf = ltrim($cpf, '0');
        
        if (strlen($cpf) != 11) {
            return null;
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($cpf, 0, 3),
            substr($cpf, 3, 3),
            substr($cpf, 6, 3),
            substr($cpf, 9, 2)
        );
    }

    protected function updateImportHeader(array $headerData): void
    {
        $this->afdImport->update([
            'cnpj' => $headerData['cnpj_cpf_employer'],
            'start_date' => $headerData['start_date'],
            'end_date' => $headerData['end_date'],
        ]);
    }
}
