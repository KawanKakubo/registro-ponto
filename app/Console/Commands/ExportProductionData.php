<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportProductionData extends Command
{
    protected $signature = 'data:export 
                            {--output= : Caminho do arquivo de saída (padrão: database/seeders/ProductionDataSeeder.php)}';

    protected $description = 'Exporta dados cadastrais para um seeder (sem registros de ponto)';

    public function handle(): int
    {
        $this->info('🔄 Exportando dados cadastrais...');
        $this->newLine();

        // Coletar dados
        $establishments = $this->exportTable('establishments', [
            'id', 'corporate_name', 'trade_name', 'cnpj', 'state_registration',
            'street', 'number', 'complement', 'neighborhood', 'city', 'state',
            'zip_code', 'phone', 'email'
        ]);
        $this->info("  ✓ Estabelecimentos: " . count($establishments));

        $departments = $this->exportTable('departments', [
            'id', 'establishment_id', 'name', 'responsible'
        ]);
        $this->info("  ✓ Departamentos: " . count($departments));

        $people = $this->exportTable('people', [
            'id', 'full_name', 'cpf', 'pis_pasep', 'ctps'
        ]);
        $this->info("  ✓ Pessoas: " . count($people));

        $registrations = $this->exportTable('employee_registrations', [
            'id', 'person_id', 'matricula', 'establishment_id', 'department_id',
            'admission_date', 'position', 'status'
        ]);
        $this->info("  ✓ Vínculos: " . count($registrations));

        $templates = $this->exportTable('work_shift_templates', [
            'id', 'name', 'description', 'type', 'is_preset', 'weekly_hours',
            'created_by', 'calculation_mode'
        ]);
        $this->info("  ✓ Modelos de jornada: " . count($templates));

        $weeklySchedules = $this->exportTable('template_weekly_schedules', [
            'id', 'template_id', 'day_of_week', 'entry_1', 'exit_1',
            'entry_2', 'exit_2', 'entry_3', 'exit_3', 'is_work_day', 'daily_hours'
        ]);
        $this->info("  ✓ Escalas semanais: " . count($weeklySchedules));

        $assignments = $this->exportTable('employee_work_shift_assignments', [
            'id', 'employee_registration_id', 'template_id', 'cycle_start_date',
            'effective_from', 'effective_until', 'assigned_by', 'custom_settings'
        ]);
        $this->info("  ✓ Atribuições de jornada: " . count($assignments));

        $this->newLine();

        // Gerar o arquivo seeder
        $outputPath = $this->option('output') ?? base_path('database/seeders/ProductionDataSeeder.php');
        
        $content = $this->generateSeederContent([
            'establishments' => $establishments,
            'departments' => $departments,
            'people' => $people,
            'registrations' => $registrations,
            'templates' => $templates,
            'weeklySchedules' => $weeklySchedules,
            'assignments' => $assignments,
        ]);

        File::put($outputPath, $content);

        $this->info("✅ Seeder gerado em: {$outputPath}");
        $this->newLine();
        $this->info("Para usar no ambiente de produção:");
        $this->line("  1. Copie o arquivo para o servidor");
        $this->line("  2. Execute: php artisan db:seed --class=ProductionDataSeeder");

        return Command::SUCCESS;
    }

    private function exportTable(string $table, array $columns): array
    {
        return DB::table($table)
            ->select($columns)
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();
    }

    private function generateSeederContent(array $data): string
    {
        $date = now()->format('Y-m-d H:i:s');
        
        $establishmentsPhp = $this->arrayToPhp($data['establishments']);
        $departmentsPhp = $this->arrayToPhp($data['departments']);
        $peoplePhp = $this->arrayToPhp($data['people']);
        $registrationsPhp = $this->arrayToPhp($data['registrations']);
        $templatesPhp = $this->arrayToPhp($data['templates']);
        $weeklySchedulesPhp = $this->arrayToPhp($data['weeklySchedules']);
        $assignmentsPhp = $this->arrayToPhp($data['assignments']);

        return <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder com dados de produção
 * 
 * Contém todos os dados cadastrais (sem registros de ponto):
 * - Estabelecimentos: {$this->count($data['establishments'])}
 * - Departamentos: {$this->count($data['departments'])}
 * - Pessoas: {$this->count($data['people'])}
 * - Vínculos empregatícios: {$this->count($data['registrations'])}
 * - Modelos de jornada: {$this->count($data['templates'])}
 * - Escalas semanais: {$this->count($data['weeklySchedules'])}
 * - Atribuições de jornada: {$this->count($data['assignments'])}
 * 
 * Gerado em: {$date}
 */
class ProductionDataSeeder extends Seeder
{
    public function run(): void
    {
        \$this->command->info('Importando dados de produção...');

        // Desabilitar verificação de chaves estrangeiras temporariamente
        DB::statement('SET session_replication_role = replica;');

        try {
            \$this->seedEstablishments();
            \$this->seedDepartments();
            \$this->seedPeople();
            \$this->seedEmployeeRegistrations();
            \$this->seedWorkShiftTemplates();
            \$this->seedTemplateWeeklySchedules();
            \$this->seedEmployeeWorkShiftAssignments();

            \$this->command->info('✅ Dados de produção importados com sucesso!');
        } finally {
            // Reabilitar verificação de chaves estrangeiras
            DB::statement('SET session_replication_role = DEFAULT;');
        }
    }

    private function seedEstablishments(): void
    {
        \$this->command->info('Importando estabelecimentos...');
        
        \$data = \$this->getEstablishmentsData();
        
        foreach (\$data as \$item) {
            DB::table('establishments')->updateOrInsert(
                ['id' => \$item['id']],
                \$item
            );
        }
        
        \$maxId = DB::table('establishments')->max('id') ?? 0;
        if (\$maxId > 0) {
            DB::statement("SELECT setval('establishments_id_seq', ?, true)", [\$maxId]);
        }
        
        \$this->command->info("  → " . count(\$data) . " estabelecimentos importados");
    }

    private function seedDepartments(): void
    {
        \$this->command->info('Importando departamentos...');
        
        \$data = \$this->getDepartmentsData();
        
        foreach (\$data as \$item) {
            DB::table('departments')->updateOrInsert(
                ['id' => \$item['id']],
                \$item
            );
        }
        
        \$maxId = DB::table('departments')->max('id') ?? 0;
        if (\$maxId > 0) {
            DB::statement("SELECT setval('departments_id_seq', ?, true)", [\$maxId]);
        }
        
        \$this->command->info("  → " . count(\$data) . " departamentos importados");
    }

    private function seedPeople(): void
    {
        \$this->command->info('Importando pessoas...');
        
        \$data = \$this->getPeopleData();
        
        foreach (\$data as \$item) {
            DB::table('people')->updateOrInsert(
                ['id' => \$item['id']],
                \$item
            );
        }
        
        \$maxId = DB::table('people')->max('id') ?? 0;
        if (\$maxId > 0) {
            DB::statement("SELECT setval('employees_id_seq', ?, true)", [\$maxId]);
        }
        
        \$this->command->info("  → " . count(\$data) . " pessoas importadas");
    }

    private function seedEmployeeRegistrations(): void
    {
        \$this->command->info('Importando vínculos empregatícios...');
        
        \$data = \$this->getEmployeeRegistrationsData();
        
        foreach (\$data as \$item) {
            DB::table('employee_registrations')->updateOrInsert(
                ['id' => \$item['id']],
                \$item
            );
        }
        
        \$maxId = DB::table('employee_registrations')->max('id') ?? 0;
        if (\$maxId > 0) {
            DB::statement("SELECT setval('employee_registrations_id_seq', ?, true)", [\$maxId]);
        }
        
        \$this->command->info("  → " . count(\$data) . " vínculos importados");
    }

    private function seedWorkShiftTemplates(): void
    {
        \$this->command->info('Importando modelos de jornada...');
        
        \$data = \$this->getWorkShiftTemplatesData();
        
        foreach (\$data as \$item) {
            DB::table('work_shift_templates')->updateOrInsert(
                ['id' => \$item['id']],
                \$item
            );
        }
        
        \$maxId = DB::table('work_shift_templates')->max('id') ?? 0;
        if (\$maxId > 0) {
            DB::statement("SELECT setval('work_shift_templates_id_seq', ?, true)", [\$maxId]);
        }
        
        \$this->command->info("  → " . count(\$data) . " modelos de jornada importados");
    }

    private function seedTemplateWeeklySchedules(): void
    {
        \$this->command->info('Importando escalas semanais...');
        
        \$data = \$this->getTemplateWeeklySchedulesData();
        
        foreach (\$data as \$item) {
            DB::table('template_weekly_schedules')->updateOrInsert(
                ['id' => \$item['id']],
                \$item
            );
        }
        
        \$maxId = DB::table('template_weekly_schedules')->max('id') ?? 0;
        if (\$maxId > 0) {
            DB::statement("SELECT setval('template_weekly_schedules_id_seq', ?, true)", [\$maxId]);
        }
        
        \$this->command->info("  → " . count(\$data) . " escalas semanais importadas");
    }

    private function seedEmployeeWorkShiftAssignments(): void
    {
        \$this->command->info('Importando atribuições de jornada...');
        
        \$data = \$this->getEmployeeWorkShiftAssignmentsData();
        
        foreach (\$data as \$item) {
            DB::table('employee_work_shift_assignments')->updateOrInsert(
                ['id' => \$item['id']],
                \$item
            );
        }
        
        \$maxId = DB::table('employee_work_shift_assignments')->max('id') ?? 0;
        if (\$maxId > 0) {
            DB::statement("SELECT setval('employee_work_shift_assignments_id_seq', ?, true)", [\$maxId]);
        }
        
        \$this->command->info("  → " . count(\$data) . " atribuições importadas");
    }

    // ========================================
    // DADOS EXPORTADOS
    // ========================================

    private function getEstablishmentsData(): array
    {
        return {$establishmentsPhp};
    }

    private function getDepartmentsData(): array
    {
        return {$departmentsPhp};
    }

    private function getPeopleData(): array
    {
        return {$peoplePhp};
    }

    private function getEmployeeRegistrationsData(): array
    {
        return {$registrationsPhp};
    }

    private function getWorkShiftTemplatesData(): array
    {
        return {$templatesPhp};
    }

    private function getTemplateWeeklySchedulesData(): array
    {
        return {$weeklySchedulesPhp};
    }

    private function getEmployeeWorkShiftAssignmentsData(): array
    {
        return {$assignmentsPhp};
    }
}
PHP;
    }

    private function arrayToPhp(array $data): string
    {
        if (empty($data)) {
            return '[]';
        }

        $lines = ["["];
        
        foreach ($data as $row) {
            $items = [];
            foreach ($row as $key => $value) {
                if (is_null($value)) {
                    $items[] = "'{$key}' => null";
                } elseif (is_bool($value)) {
                    $items[] = "'{$key}' => " . ($value ? 'true' : 'false');
                } elseif (is_numeric($value) && !is_string($value)) {
                    $items[] = "'{$key}' => {$value}";
                } else {
                    $escaped = addslashes((string) $value);
                    $items[] = "'{$key}' => '{$escaped}'";
                }
            }
            $lines[] = "            [" . implode(", ", $items) . "],";
        }
        
        $lines[] = "        ]";
        
        return implode("\n", $lines);
    }

    private function count(array $data): int
    {
        return count($data);
    }
}
