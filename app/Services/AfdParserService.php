<?php

namespace App\Services;

use App\Models\AfdImport;
use App\Services\AfdParsers\AfdParserFactory;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de parsing de arquivos AFD
 * 
 * Orquestra o processo de importação usando a arquitetura de parsers modulares
 */
class AfdParserService
{
    /**
     * Processa o arquivo AFD usando o parser apropriado
     * 
     * @param string $filePath Caminho do arquivo
     * @param AfdImport $afdImport Registro de importação
     * @param string|null $formatHint Dica opcional do formato
     * @return array Resultado do processamento
     */
    public function parse(string $filePath, AfdImport $afdImport, ?string $formatHint = null): array
    {
        try {
            // Converte para caminho absoluto se necessário
            // Se já tiver storage_path() no início, usa direto. Senão, adiciona.
            if (str_starts_with($filePath, '/')) {
                $fullPath = $filePath;
            } elseif (str_starts_with($filePath, 'storage/app/')) {
                // Caminho já contém storage/app/, só adiciona o base_path
                $fullPath = base_path($filePath);
            } else {
                // Caminho relativo normal
                $fullPath = storage_path('app/' . $filePath);
            }
            
            if (!file_exists($fullPath)) {
                throw new \Exception("Arquivo não encontrado: {$fullPath}");
            }
            
            Log::info("AfdParserService: Iniciando processamento do arquivo {$fullPath}");

            // Factory cria o parser apropriado baseado no arquivo ou dica
            $parser = AfdParserFactory::createParser($fullPath, $formatHint);
            
            Log::info("AfdParserService: Usando parser {$parser->getFormatName()}");

            // Delega o processamento para o parser específico
            return $parser->parse($fullPath, $afdImport);

        } catch (\Exception $e) {
            $afdImport->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("AfdParserService: Erro ao processar arquivo - " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'imported' => 0,
                'skipped' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Retorna lista de formatos suportados
     *
     * @return array
     */
    public function getSupportedFormats(): array
    {
        return AfdParserFactory::getSupportedFormats();
    }
}
