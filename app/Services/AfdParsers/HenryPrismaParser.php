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
            $relevantLineCount = 0; // Conta apenas linhas relevantes (não headers)

            while (($line = fgets($handle)) !== false && $lineCount < 200) {
                $line = trim($line);
                $lineCount++;
                
                if (empty($line) || strlen($line) < 25) {
                    continue;
                }

                // Pula headers e linhas muito longas (>100 chars)
                // Focamos apenas nas linhas de marcação de ponto
                if (strlen($line) > 100) {
                    continue;
                }

                $relevantLineCount++;

                // Prisma tem características MUITO específicas:
                // 1. Linhas curtas de exatamente 38-40 caracteres
                // 2. Termina com 4 caracteres hex (checksum)
                // 3. Checksum frequentemente contém letras A-F
                
                // Verifica se tem comprimento típico do Prisma
                if (strlen($line) >= 37 && strlen($line) <= 41) {
                    // Verifica checksum hex (últimos 4 caracteres)
                    $lastChars = strtoupper(substr($line, -4));
                    
                    if (ctype_xdigit($lastChars)) {
                        // Conta quantas letras tem no checksum
                        $letterCount = strlen(preg_replace('/[0-9]/', '', $lastChars));
                        
                        // Prisma tem checksums com letras A-F frequentemente
                        if ($letterCount >= 1) {
                            $prismaLineCount += 3; // Peso alto para linhas com letras no checksum
                        } else {
                            $prismaLineCount += 1; // Peso baixo para linhas sem letras
                        }
                    }
                }
            }

            fclose($handle);
            
            if ($relevantLineCount > 10) {
                // Score baseado apenas em linhas relevantes
                $score = $prismaLineCount / ($relevantLineCount * 3);
                
                // Se mais de 40% das linhas têm características Prisma, consideramos Prisma
                // (ajustado de 0.5 para 0.4 para melhor detecção)
                return $score > 0.4;
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

            // Formato: NSR(9) + Tipo(1) + Data(8) + Hora(4) + PIS(11) + Checksum(4)
            // NSR: primeiros 9 dígitos
            $nsr = substr($dataWithoutChecksum, 0, 9);
            
            // Tipo de registro (posição 9)
            $recordType = substr($dataWithoutChecksum, 9, 1);
            
            // Se não for tipo 3 (marcação de ponto), pula
            if ($recordType !== '3') {
                $this->skippedCount++;
                return;
            }
            
            // Data: 8 dígitos após NSR e tipo (posição 10-17)
            $dateStr = substr($dataWithoutChecksum, 10, 8);
            // Hora: 4 dígitos (posição 18-21)
            $timeStr = substr($dataWithoutChecksum, 18, 4);
            
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

            // PIS: 12 dígitos após data/hora (posição 22-33)
            // Formato Henry Prisma SEMPRE usa 12 dígitos, com zero à esquerda quando necessário
            // Extraímos 12 dígitos e pegamos os últimos 11 (o PIS real)
            $pisRaw = substr($dataWithoutChecksum, 22, 12);
            
            if (!is_numeric($pisRaw)) {
                $this->skippedCount++;
                return;
            }

            // PIS real são os últimos 11 dígitos (ignora o primeiro que é padding)
            $pis = substr($pisRaw, 1, 11);
            
            if (!is_numeric($pis) || strlen($pis) != 11) {
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

            // Usa o NSR real do arquivo
            $this->createTimeRecord($employee, $recordedAt, $nsr, '3');

        } catch (\Exception $e) {
            $this->addError("Linha {$lineNumber}: " . $e->getMessage());
            $this->skippedCount++;
        }
    }
}
