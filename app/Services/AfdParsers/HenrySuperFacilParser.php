<?php

namespace App\Services\AfdParsers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Parser para arquivos AFD do modelo Henry Super Fácil
 * 
 * Formato com data/hora compacta (ddmmyyyyHHMM)
 * Utiliza PIS/PASEP para identificação de funcionários
 */
class HenrySuperFacilParser extends BaseAfdParser
{
    public function getFormatName(): string
    {
        return 'Henry Super Fácil';
    }

    public function getFormatDescription(): string
    {
        return 'Formato AFD Henry Super Fácil - utiliza PIS/PASEP para identificação';
    }

    public function canParse(string $filePath): bool
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) return false;

            $lineCount = 0;
            $hasType3 = false;
            $hasCompactDate = false;
            $prismaCharacteristics = 0; // Contador de características do Prisma
            $superFacilMatches = 0; // Contador de matches Super Fácil

            while (($line = fgets($handle)) !== false && $lineCount < 150) {
                $line = trim($line);
                if (empty($line) || strlen($line) < 30) {
                    $lineCount++;
                    continue;
                }

                // IMPORTANTE: Detectar características do Prisma para rejeitar
                // Prisma tem linhas curtas (~39 chars) terminando com checksum hex contendo letras
                if (strlen($line) >= 37 && strlen($line) <= 41) {
                    $lastChars = strtoupper(substr($line, -4));
                    if (ctype_xdigit($lastChars)) {
                        $letterCount = strlen(preg_replace('/[0-9]/', '', $lastChars));
                        if ($letterCount >= 2) {
                            // Tem forte característica de Prisma
                            $prismaCharacteristics++;
                        }
                    }
                }

                // NSR (9 dígitos) + tipo (1) + data compacta (12: ddmmyyyyHHMM)
                if (is_numeric(substr($line, 0, 9))) {
                    $recordType = substr($line, 9, 1);
                    
                    if ($recordType === '3') {
                        $hasType3 = true;
                        
                        // Verifica formato de data compacta: 12 dígitos (ddmmyyyyHHMM)
                        $dateStr = substr($line, 10, 12);
                        if (is_numeric($dateStr) && strlen($dateStr) === 12) {
                            $hasCompactDate = true;
                            
                            // PIS na sequência (12 dígitos)
                            $possiblePis = substr($line, 22, 12);
                            if (is_numeric($possiblePis)) {
                                $superFacilMatches++;
                            }
                        }
                    }
                    
                    // Tipo 4 ou 5 (inclusão/alteração) com 'I' ou 'A'
                    if (in_array($recordType, ['4', '5'])) {
                        $dateStr = substr($line, 10, 12);
                        if (is_numeric($dateStr) && strlen($dateStr) === 12) {
                            $action = substr($line, 22, 1);
                            if (in_array($action, ['I', 'A'])) {
                                $superFacilMatches++;
                            }
                        }
                    }
                }
                
                $lineCount++;
            }

            fclose($handle);

            // Se tem muitas características Prisma (>10) e poucas do Super Fácil, rejeita
            if ($prismaCharacteristics > 10 && $superFacilMatches < 5) {
                return false;
            }
            
            return $hasType3 && $hasCompactDate && $superFacilMatches > 0;

            fclose($handle);
            return $hasType3 && $hasCompactDate;

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

            if (empty($line) || strlen($line) < 22) {
                continue;
            }

            $recordType = substr($line, 9, 1);

            switch ($recordType) {
                case '3':
                    $this->parseTimeRecord($line, $lineNumber);
                    break;
                
                case '4':
                case '5':
                    Log::debug("Henry SF: Registro tipo {$recordType} (inclusão/alteração) na linha {$lineNumber}");
                    break;
                
                default:
                    Log::debug("Henry SF: Tipo de registro '{$recordType}' na linha {$lineNumber}");
                    break;
            }
        }

        fclose($handle);
    }

    protected function parseTimeRecord(string $line, int $lineNumber): void
    {
        try {
            $nsr = trim(substr($line, 0, 9));
            
            // Data/hora compacta: ddmmyyyyHHMM (12 dígitos)
            $dateTimeStr = substr($line, 10, 12);
            
            if (!is_numeric($dateTimeStr) || strlen($dateTimeStr) !== 12) {
                $this->addError("Linha {$lineNumber}: Data/hora inválida");
                $this->skippedCount++;
                return;
            }

            // Parse: ddmmyyyyHHMM
            $day = substr($dateTimeStr, 0, 2);
            $month = substr($dateTimeStr, 2, 2);
            $year = substr($dateTimeStr, 4, 4);
            $hour = substr($dateTimeStr, 8, 2);
            $minute = substr($dateTimeStr, 10, 2);

            try {
                $recordedAt = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    "{$year}-{$month}-{$day} {$hour}:{$minute}:00"
                );
            } catch (\Exception $e) {
                $this->addError("Linha {$lineNumber}: Data/hora inválida - {$dateTimeStr}");
                $this->skippedCount++;
                return;
            }

            // PIS na posição 22, 12 caracteres
            $pisRaw = trim(substr($line, 22, 12));
            $pis = $this->normalizePis($pisRaw);
            
            if (!$pis) {
                $this->addError("Linha {$lineNumber}: PIS inválido '{$pisRaw}'");
                $this->skippedCount++;
                return;
            }

            // Busca por PIS
            $employee = $this->findEmployee($pis, null, null);
            
            if (!$employee) {
                $this->addError("Linha {$lineNumber}: Colaborador com PIS {$pis} não encontrado");
                $this->skippedCount++;
                return;
            }

            $this->createTimeRecord($employee, $recordedAt, $nsr, '3');

        } catch (\Exception $e) {
            $this->addError("Linha {$lineNumber}: " . $e->getMessage());
            $this->skippedCount++;
        }
    }
}
