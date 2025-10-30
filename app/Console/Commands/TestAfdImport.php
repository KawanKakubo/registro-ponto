<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AfdParserService;
use App\Models\AfdImport;
use Illuminate\Support\Facades\Storage;

class TestAfdImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afd:test-import {file : Caminho do arquivo AFD} {--format= : Formato específico (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a importação de um arquivo AFD com detecção automática de formato';

    protected AfdParserService $parserService;

    public function __construct(AfdParserService $parserService)
    {
        parent::__construct();
        $this->parserService = $parserService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $formatHint = $this->option('format');

        // Verifica se o arquivo existe
        if (!file_exists($filePath)) {
            $this->error("❌ Arquivo não encontrado: {$filePath}");
            return 1;
        }

        $this->info("🔍 Analisando arquivo: " . basename($filePath));
        $this->info("📦 Tamanho: " . $this->formatBytes(filesize($filePath)));
        
        if ($formatHint) {
            $this->warn("⚠️  Formato forçado: {$formatHint}");
        } else {
            $this->info("🤖 Detecção automática ativada");
        }

        $this->newLine();

        // Lista formatos suportados
        $this->info("📋 Formatos suportados:");
        $formats = $this->parserService->getSupportedFormats();
        foreach ($formats as $format) {
            $this->line("   • {$format['name']}: {$format['description']}");
        }

        $this->newLine();

        // Cria registro de importação
        $import = AfdImport::create([
            'file_name' => basename($filePath),
            'file_path' => $filePath,
            'file_size' => filesize($filePath),
            'status' => 'processing',
            'format_hint' => $formatHint,
            'imported_at' => now(),
        ]);

        $this->info("📝 Registro de importação criado (ID: {$import->id})");
        $this->newLine();

        // Barra de progresso
        $this->output->write("🔄 Processando arquivo... ");

        // Processa o arquivo
        $startTime = microtime(true);
        $result = $this->parserService->parse($filePath, $import, $formatHint);
        $duration = round(microtime(true) - $startTime, 2);

        $this->info("✅ Concluído em {$duration}s");
        $this->newLine();

        // Recarrega o import para pegar dados atualizados
        $import->refresh();

        // Exibe resultados
        if ($result['success']) {
            $this->components->success("✅ Importação concluída com sucesso!");
            
            $this->newLine();
            $this->info("📊 Estatísticas:");
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Formato Detectado', $result['format'] ?? 'N/A'],
                    ['Registros Importados', $result['imported']],
                    ['Registros Pulados', $result['skipped']],
                    ['Total de Erros', count($result['errors'])],
                    ['Status Final', $import->status],
                    ['Tempo de Processamento', "{$duration}s"],
                ]
            );

            if ($result['skipped'] > 0) {
                $this->newLine();
                $this->warn("⚠️  {$result['skipped']} registros foram pulados (duplicados ou inválidos)");
            }

            if (!empty($result['errors'])) {
                $this->newLine();
                $this->error("❌ Erros encontrados:");
                foreach (array_slice($result['errors'], 0, 10) as $error) {
                    $this->line("   • {$error}");
                }
                if (count($result['errors']) > 10) {
                    $this->line("   ... e mais " . (count($result['errors']) - 10) . " erros");
                }
            }

        } else {
            $this->components->error("❌ Falha na importação!");
            $this->error("Erro: " . ($result['error'] ?? 'Erro desconhecido'));
            
            if (!empty($result['errors'])) {
                $this->newLine();
                $this->error("Detalhes dos erros:");
                foreach (array_slice($result['errors'], 0, 10) as $error) {
                    $this->line("   • {$error}");
                }
            }

            return 1;
        }

        $this->newLine();
        $this->info("💾 Para ver os registros importados:");
        $this->line("   php artisan tinker");
        $this->line("   TimeRecord::where('afd_file_name', '{$import->file_name}')->count()");

        return 0;
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
