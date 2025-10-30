<?php

namespace App\Services\AfdParsers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Parser para arquivos AFD do modelo Henry Prisma Super Fácil
 * 
 * Formato proprietário com estrutura hexadecimal
 * Utiliza PIS/PASEP para identificação de funcionários
 */
class HenryPrismaParser extends BaseAfdParser
{
    public function getFormatName(): string
    {
        return 'Henry Prisma';
    }

    public function getFormatDescription(): string
    {
        return 'Formato AFD Henry Prisma Super Fácil (proprietário) - utiliza PIS/PASEP para identificação';
    }

    public function canParse(string $filePath): bool
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) return false;

            $lineCount = 0;
            $prismaLineCount = 0;
            $avgLength = 0;
            $totalLength = 0;

            while (($line = fgets($handle)) !== false && $lineCount < 100) {
                $line = trim($line);
                if (empty($line) || strlen($line) < 25) {
                    $lineCount++;
                    continue;
                }

                $totalLength += strlen($line);

                // Prisma tem características MUITO específicas:
                // 1. Comprimento médio das linhas: 38-40 caracteres
                // 2. Termina com 4 caracteres hex com LETRAS (não apenas números)
                // 3. Checksum contém letras maiúsculas A-F
                
                // Verifica checksum hex (últimos 4)
                $lastChars = strtoupper(substr($line, -4));
                if (ctype_xdigit($lastChars)) {
                    // Conta quantas letras tem no checksum
                    $letterCount = strlen(preg_replace('/[0-9]/', '', $lastChars));
                    
                    // Prisma pode ter ou não letras, mas tem comprimento específico
                    // Verifica comprimento da linha (Prisma é ~38-39)
                    if (strlen($line) >= 37 && strlen($line) <= 40) {
                        // Prisma tende a ter checksum com letras (mas não é obrigatório)
                        if ($letterCount >= 1) {
                            $prismaLineCount += 2; // Peso maior para linhas com letras
                        } else {
                            $prismaLineCount += 1; // Peso menor para linhas sem letras
                        }
                    }
                }
                
                $lineCount++;
            }

            fclose($handle);
            
            if ($lineCount > 0) {
                $avgLength = $totalLength / $lineCount;
                // Prisma tem linhas médias de ~36-39 caracteres
                // Super Fácil tem linhas médias de ~35 caracteres
                $hasRightLength = $avgLength >= 36 && $avgLength <= 40;
                
                // Calculamos um score ponderado
                $score = $prismaLineCount / ($lineCount * 2); // Normaliza considerando peso
                
                // Se tem comprimento certo E score > 0.5, é Prisma
                return $hasRightLength && $score > 0.5;
            }
            
            return false;

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

            if (empty($line) || strlen($line) < 26) {
                continue;
            }

            // Formato Prisma: ddmmyyyyHHMM(12) + PIS(11) + checksum(4)
            // Total mínimo: 27 caracteres
            
            $this->parsePrismaTimeRecord($line, $lineNumber);
        }

        fclose($handle);
    }

    protected function parsePrismaTimeRecord(string $line, int $lineNumber): void
    {
        try {
            // Remove checksum (últimos 4 caracteres hex)
            $dataWithoutChecksum = substr($line, 0, -4);
            
            if (strlen($dataWithoutChecksum) < 23) {
                $this->skippedCount++;
                return;
            }

            // Data: primeiros 8 dígitos (ddmmyyyy)
            $dateStr = substr($dataWithoutChecksum, 0, 8);
            // Hora: próximos 4 dígitos (HHMM)
            $timeStr = substr($dataWithoutChecksum, 8, 4);
            
            if (!is_numeric($dateStr) || !is_numeric($timeStr)) {
                $this->skippedCount++;
                return;
            }

            $day = substr($dateStr, 0, 2);
            $month = substr($dateStr, 2, 2);
            $year = substr($dateStr, 4, 4);
            $hour = substr($timeStr, 0, 2);
            $minute = substr($timeStr, 2, 2);

            try {
                $recordedAt = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    "{$year}-{$month}-{$day} {$hour}:{$minute}:00"
                );
            } catch (\Exception $e) {
                $this->addError("Linha {$lineNumber}: Data/hora inválida");
                $this->skippedCount++;
                return;
            }

            // PIS: próximos 11 dígitos após data/hora
            $pisRaw = substr($dataWithoutChecksum, 12, 11);
            
            if (!is_numeric($pisRaw)) {
                $this->skippedCount++;
                return;
            }

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

            $nsr = str_pad($lineNumber, 9, '0', STR_PAD_LEFT);
            $this->createTimeRecord($employee, $recordedAt, $nsr, 'PR');

        } catch (\Exception $e) {
            $this->addError("Linha {$lineNumber}: " . $e->getMessage());
            $this->skippedCount++;
        }
    }
}
