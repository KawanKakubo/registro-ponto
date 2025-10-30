<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AfdParserService;

class ListAfdFormats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afd:formats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todos os formatos de AFD suportados pelo sistema';

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
        $this->info("🔧 Formatos de AFD Suportados");
        $this->newLine();

        $formats = $this->parserService->getSupportedFormats();

        $tableData = [];
        foreach ($formats as $index => $format) {
            $tableData[] = [
                $index + 1,
                $format['name'],
                $format['description'],
                $this->getIdentifier($format['class']),
            ];
        }

        $this->table(
            ['#', 'Formato', 'Descrição', 'Busca por'],
            $tableData
        );

        $this->newLine();
        $this->info("💡 Dicas:");
        $this->line("   • O sistema detecta automaticamente o formato do arquivo");
        $this->line("   • Você pode forçar um formato específico usando a opção --format");
        $this->line("   • Exemplo: php artisan afd:test-import arquivo.txt --format=henry-prisma");

        $this->newLine();
        $this->info("📋 Format Hints disponíveis:");
        $hints = [
            'dixi' => 'DIXI',
            'henry-super-facil, henry-sf, super-facil' => 'Henry Super Fácil',
            'henry-prisma, prisma' => 'Henry Prisma',
            'henry-orion-5, henry-orion, orion-5, orion' => 'Henry Orion 5',
        ];

        foreach ($hints as $hint => $format) {
            $this->line("   • {$hint} → {$format}");
        }

        return 0;
    }

    protected function getIdentifier(string $class): string
    {
        $map = [
            'DixiParser' => 'CPF',
            'HenrySuperFacilParser' => 'PIS/PASEP',
            'HenryPrismaParser' => 'PIS/PASEP',
            'HenryOrion5Parser' => 'Matrícula',
        ];

        foreach ($map as $parser => $identifier) {
            if (str_contains($class, $parser)) {
                return $identifier;
            }
        }

        return 'N/A';
    }
}
