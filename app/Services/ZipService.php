<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ZipService
{
    /**
     * Cria um arquivo ZIP com múltiplos PDFs
     * 
     * @param array $pdfs Array de arrays com ['filename' => string, 'content' => string]
     * @param string $zipName Nome do arquivo ZIP (sem extensão)
     * @return string Caminho completo do arquivo ZIP criado
     * @throws \Exception
     */
    public function createZipFromPdfs(array $pdfs, string $zipName): string
    {
        if (empty($pdfs)) {
            throw new \Exception('Nenhum PDF fornecido para criar o ZIP');
        }

        // Criar pasta temporária se não existir
        $tempDir = storage_path('app/temp/timesheets');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Nome do arquivo ZIP
        $zipFileName = Str::slug($zipName) . '_' . time() . '.zip';
        $zipPath = $tempDir . '/' . $zipFileName;

        // Criar ZIP
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Não foi possível criar o arquivo ZIP');
        }

        // Adicionar cada PDF ao ZIP
        foreach ($pdfs as $pdf) {
            if (!isset($pdf['filename']) || !isset($pdf['content'])) {
                continue;
            }

            $zip->addFromString($pdf['filename'], $pdf['content']);
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Remove arquivos ZIP antigos da pasta temporária
     * 
     * @param int $olderThanMinutes Remover arquivos mais antigos que X minutos (padrão: 60)
     * @return int Número de arquivos removidos
     */
    public function cleanOldZipFiles(int $olderThanMinutes = 60): int
    {
        $tempDir = storage_path('app/temp/timesheets');
        
        if (!file_exists($tempDir)) {
            return 0;
        }

        $files = glob($tempDir . '/*.zip');
        $now = time();
        $removed = 0;

        foreach ($files as $file) {
            $fileTime = filemtime($file);
            $ageMinutes = ($now - $fileTime) / 60;

            if ($ageMinutes > $olderThanMinutes) {
                if (unlink($file)) {
                    $removed++;
                }
            }
        }

        return $removed;
    }
}
