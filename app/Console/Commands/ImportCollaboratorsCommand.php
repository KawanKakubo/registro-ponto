<?php

namespace App\Console\Commands;

use App\Jobs\ImportCollaboratorsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportCollaboratorsCommand extends Command
{
    protected $signature = 'colaboradores:import {file} {--user-id=1}';
    protected $description = 'Importa colaboradores (pessoas) a partir de um arquivo CSV';

    public function handle()
    {
        $file = $this->argument('file');
        $userId = $this->option('user-id');

        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return 1;
        }

        $this->info("Iniciando importação de colaboradores...");
        $this->info("Arquivo: {$file}");

        ImportCollaboratorsJob::dispatch($file, $userId);

        $this->info("Job de importação de colaboradores disparado com sucesso!");
        return 0;
    }
}
