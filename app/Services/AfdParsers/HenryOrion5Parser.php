<?php

namespace App\Services\AfdParsers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Parser para arquivos AFD do modelo Henry Orion 5
 * 
 * Formato simplificado com matrícula
 * Utiliza MATRÍCULA para identificação de funcionários
 */
class HenryOrion5Parser extends BaseAfdParser
{
    public function getFormatName(): string
    {
        return 'Henry Orion 5';
    }

    public function getFormatDescription(): string
    {
        return 'Formato AFD Henry Orion 5 - utiliza matrícula para identificação';
    }

    public function canParse(string $filePath): bool
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) return false;

            $lineCount = 0;
            $matchCount = 0;

            while (($line = fgets($handle)) !== false && $lineCount < 50) {
                $line = trim($line);
                if (empty($line)) continue;

                $lineCount++;

                // Formato Orion 5: "01 N 0   DD/MM/YYYY HH:MM:SS MATRICULA"
                // Exemplo: "01 N 0   10/09/2025 16:03:11 00000000000000003268"
                if (preg_match('/^01\s+[NS]\s+\d+\s+\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2}\s+\d{20}/', $line)) {
                    $matchCount++;
                }
            }

            fclose($handle);
            
            // Considera Orion 5 se mais de 70% das linhas batem com o padrão
            return $lineCount > 0 && ($matchCount / $lineCount) > 0.7;

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

            if (empty($line) || strlen($line) < 16) {
                continue;
            }

            $this->parseOrion5TimeRecord($line, $lineNumber);
        }

        fclose($handle);
    }

    protected function parseOrion5TimeRecord(string $line, int $lineNumber): void
    {
        try {
            // Formato Orion 5: "01 N 0   DD/MM/YYYY HH:MM:SS MATRICULA"
            // Exemplo: "01 N 0   10/09/2025 16:03:11 00000000000000003268"
            
            if (!preg_match('/^01\s+[NS]\s+\d+\s+(\d{2}\/\d{2}\/\d{4})\s+(\d{2}:\d{2}:\d{2})\s+(\d{20})/', $line, $matches)) {
                $this->skippedCount++;
                return;
            }

            $dateStr = $matches[1];  // DD/MM/YYYY
            $timeStr = $matches[2];  // HH:MM:SS
            $matricula = ltrim($matches[3], '0');  // Remove zeros à esquerda
            
            if (empty($matricula)) {
                $matricula = '0';  // Se tudo for zero, mantém um zero
            }

            // Parse da data e hora
            try {
                $recordedAt = Carbon::createFromFormat('d/m/Y H:i:s', "{$dateStr} {$timeStr}");
            } catch (\Exception $e) {
                $this->addError("Linha {$lineNumber}: Data/hora inválida");
                $this->skippedCount++;
                return;
            }

            // Busca funcionário pela matrícula
            $employee = $this->findEmployee(null, $matricula, null);
            
            if (!$employee) {
                $this->addError("Linha {$lineNumber}: Colaborador com matrícula '{$matricula}' não encontrado");
                $this->skippedCount++;
                return;
            }

            $nsr = str_pad($lineNumber, 9, '0', STR_PAD_LEFT);
            $this->createTimeRecord($employee, $recordedAt, $nsr, 'O5');

        } catch (\Exception $e) {
            $this->addError("Linha {$lineNumber}: " . $e->getMessage());
            $this->skippedCount++;
        }
    }
}
