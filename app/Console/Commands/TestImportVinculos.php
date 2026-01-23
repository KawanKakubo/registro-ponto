<?php

namespace App\Console\Commands;

use App\Jobs\ImportVinculosJob;
use App\Models\VinculoImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestImportVinculos extends Command
{
    protected $signature = 'vinculos:test-import {file} {--user-id=1}';

    protected $description = 'Testa a importação de vínculos diretamente (sem fila)';

    public function handle()
    {
        $file = $this->argument('file');
        $userId = $this->option('user-id');

        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return 1;
        }

        // Copiar para storage temporário
        $storagePath = 'imports/' . basename($file);
        Storage::put($storagePath, file_get_contents($file));

        // Criar registro de importação
        $import = VinculoImport::create([
            'filename' => basename($file),
            'csv_path' => $storagePath,
            'user_id' => $userId,
            'status' => 'processing',
        ]);

        $this->info("Iniciando importação ID: {$import->id}");
        $this->info("Arquivo: {$file}");

        // Executar o job diretamente (sem fila)
        $job = new ImportVinculosJob($storagePath, $import->id, $userId);
        $job->handle();

        // Recarregar para ver resultados
        $import->refresh();

        $this->info("\n=== RESULTADOS ===");
        $this->info("Status: {$import->status}");
        $this->info("Pessoas criadas: {$import->pessoas_criadas}");
        $this->info("Pessoas atualizadas: {$import->pessoas_atualizadas}");
        $this->info("Vínculos criados: {$import->vinculos_criados}");
        $this->info("Vínculos atualizados: {$import->vinculos_atualizados}");
        $this->info("Jornadas associadas: {$import->jornadas_associadas}");
        $this->info("Erros: {$import->errors_count}");

        if ($import->errors_count > 0 && $import->errors_data) {
            $this->warn("\n=== ERROS ===");
            $errors = json_decode($import->errors_data, true);
            foreach (array_slice($errors, 0, 5) as $error) {
                $this->line("Linha {$error['line']}: {$error['error']}");
            }
            if (count($errors) > 5) {
                $this->line("... e mais " . (count($errors) - 5) . " erros");
            }
        }

        return 0;
    }
}
