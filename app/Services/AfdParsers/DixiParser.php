<?php

namespace App\Services\AfdParsers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Parser para arquivos AFD do modelo DIXI
 * 
 * Formato baseado na Portaria 1510/2009 padrão
 * Utiliza CPF para identificação de funcionários
 */
class DixiParser extends BaseAfdParser
{
    public function getFormatName(): string
    {
        return 'DIXI';
    }

    public function getFormatDescription(): string
    {
        return 'Formato AFD padrão Portaria 1510/2009 (DIXI) - utiliza CPF para identificação';
    }

    public function canParse(string $filePath): bool
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) return false;

            $lineCount = 0;
            $hasType3 = false;
            $hasValidCpf = false;

            while (($line = fgets($handle)) !== false && $lineCount < 50) {
                $line = trim($line);
                if (empty($line) || strlen($line) < 46) {
                    $lineCount++;
                    continue;
                }

                // Verifica estrutura: NSR (9) + tipo (1) + data ISO (24)
                if (is_numeric(substr($line, 0, 9))) {
                    $recordType = substr($line, 9, 1);
                    
                    // Tipo 3 - marcação de ponto
                    if ($recordType === '3') {
                        $hasType3 = true;
                        
                        // Verifica data/hora em formato ISO (YYYY-MM-DD HH:MM:SS)
                        $dateTimeStr = substr($line, 10, 24);
                        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $dateTimeStr)) {
                            // CPF na posição 34, 12 caracteres
                            $possibleCpf = substr($line, 34, 12);
                            if (is_numeric($possibleCpf)) {
                                $hasValidCpf = true;
                                fclose($handle);
                                return true;
                            }
                        }
                    }
                }
                
                $lineCount++;
            }

            fclose($handle);
            return $hasType3 && $hasValidCpf;

        } catch (\Exception $e) {
            return false;
        }
    }

    protected function processFile(string $filePath): void
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Não foi possível abrir o arquivo");
        }

        $lineNumber = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $line = trim($line);

            if (empty($line) || strlen($line) < 10) {
                continue;
            }

            $recordType = substr($line, 9, 1);

            switch ($recordType) {
                case '1':
                    $this->parseHeaderRecord($line);
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
                
                default:
                    Log::debug("DIXI: Tipo de registro '{$recordType}' na linha {$lineNumber}");
                    break;
            }
        }

        fclose($handle);
    }

    protected function parseHeaderRecord(string $line): void
    {
        $headerData = [
            'cnpj_cpf_employer' => trim(substr($line, 10, 14)),
            'start_date' => $this->parseDateTime(substr($line, 206, 24)),
            'end_date' => $this->parseDateTime(substr($line, 230, 24)),
        ];

        $this->updateImportHeader($headerData);
        Log::info("DIXI Header: CNPJ {$headerData['cnpj_cpf_employer']}");
    }

    protected function parseCompanyRecord(string $line): void
    {
        $cnpj = trim(substr($line, 11, 14));
        Log::debug("DIXI: Registro de empresa CNPJ: {$cnpj}");
    }

    protected function parseTimeRecord(string $line, int $lineNumber): void
    {
        try {
            $nsr = trim(substr($line, 0, 9));
            $dateTimeStr = substr($line, 10, 24);
            $recordedAt = $this->parseDateTime($dateTimeStr);
            
            if (!$recordedAt) {
                $this->addError("Linha {$lineNumber}: Data/hora inválida");
                $this->skippedCount++;
                return;
            }

            // CPF está na posição 35 (índice PHP 0-based), 12 caracteres
            // Posição 10 = tipo, 11-34 = data ISO (24 chars), 35-46 = CPF (12 chars)
            $cpfRaw = trim(substr($line, 34, 12));
            $cpf = $this->normalizeCpf($cpfRaw);
            
            if (!$cpf) {
                $this->addError("Linha {$lineNumber}: CPF inválido '{$cpfRaw}'");
                $this->skippedCount++;
                return;
            }

            // Busca por CPF
            $employee = $this->findEmployee(null, null, $cpf);
            
            if (!$employee) {
                $this->addError("Linha {$lineNumber}: Colaborador com CPF {$cpf} não encontrado");
                $this->skippedCount++;
                return;
            }

            $this->createTimeRecord($employee, $recordedAt, $nsr, '3');

        } catch (\Exception $e) {
            $this->addError("Linha {$lineNumber}: " . $e->getMessage());
            $this->skippedCount++;
        }
    }

    protected function parseTimeRecordRepP(string $line, int $lineNumber): void
    {
        try {
            $nsr = trim(substr($line, 0, 9));
            $dateTimeStr = substr($line, 10, 24);
            $recordedAt = $this->parseDateTime($dateTimeStr);
            
            if (!$recordedAt) {
                $this->addError("Linha {$lineNumber}: Data/hora inválida");
                $this->skippedCount++;
                return;
            }

            // CPF está na posição 35 (índice PHP 0-based), 12 caracteres
            $cpfRaw = trim(substr($line, 34, 12));
            $cpf = $this->normalizeCpf($cpfRaw);
            
            if (!$cpf) {
                $this->addError("Linha {$lineNumber}: CPF inválido");
                $this->skippedCount++;
                return;
            }

            $employee = $this->findEmployee(null, null, $cpf);
            
            if (!$employee) {
                $this->addError("Linha {$lineNumber}: Colaborador com CPF {$cpf} não encontrado");
                $this->skippedCount++;
                return;
            }

            $this->createTimeRecord($employee, $recordedAt, $nsr, '7');

        } catch (\Exception $e) {
            $this->addError("Linha {$lineNumber}: " . $e->getMessage());
            $this->skippedCount++;
        }
    }
}
