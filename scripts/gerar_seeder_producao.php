#!/usr/bin/env php
<?php

/**
 * Script para gerar o ProductionDataSeeder com todos os dados atualizados
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Gerando ProductionDataSeeder atualizado...\n\n";

$outputFile = __DIR__ . '/../database/seeders/ProductionDataSeeder.php';

// Contadores
$counts = [
    'establishments' => DB::table('establishments')->count(),
    'departments' => DB::table('departments')->count(),
    'people' => DB::table('people')->count(),
    'employee_registrations' => DB::table('employee_registrations')->count(),
    'work_shift_templates' => DB::table('work_shift_templates')->count(),
    'template_weekly_schedules' => DB::table('template_weekly_schedules')->count(),
    'employee_work_shift_assignments' => DB::table('employee_work_shift_assignments')->count(),
];

echo "Dados a exportar:\n";
foreach ($counts as $table => $count) {
    echo "  - {$table}: {$count}\n";
}
echo "\n";

// Header do arquivo
$output = "<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder com dados de produção ATUALIZADOS
 * 
 * Contém todos os dados cadastrais com jornadas corrigidas:
 * - Estabelecimentos: {$counts['establishments']}
 * - Departamentos: {$counts['departments']}
 * - Pessoas: {$counts['people']}
 * - Vínculos empregatícios: {$counts['employee_registrations']}
 * - Modelos de jornada: {$counts['work_shift_templates']}
 * - Escalas semanais: {$counts['template_weekly_schedules']}
 * - Atribuições de jornada: {$counts['employee_work_shift_assignments']}
 * 
 * Gerado em: " . now()->format('Y-m-d H:i:s') . "
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
            \$this->seedAdminUser();

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
            DB::statement(\"SELECT setval('establishments_id_seq', ?, true)\", [\$maxId]);
        }
        
        \$this->command->info('  → ' . count(\$data) . ' estabelecimentos importados');
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
            DB::statement(\"SELECT setval('departments_id_seq', ?, true)\", [\$maxId]);
        }
        
        \$this->command->info('  → ' . count(\$data) . ' departamentos importados');
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
            DB::statement(\"SELECT setval('employees_id_seq', ?, true)\", [\$maxId]);
        }
        
        \$this->command->info('  → ' . count(\$data) . ' pessoas importadas');
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
            DB::statement(\"SELECT setval('employee_registrations_id_seq', ?, true)\", [\$maxId]);
        }
        
        \$this->command->info('  → ' . count(\$data) . ' vínculos importados');
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
            DB::statement(\"SELECT setval('work_shift_templates_id_seq', ?, true)\", [\$maxId]);
        }
        
        \$this->command->info('  → ' . count(\$data) . ' modelos de jornada importados');
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
            DB::statement(\"SELECT setval('template_weekly_schedules_id_seq', ?, true)\", [\$maxId]);
        }
        
        \$this->command->info('  → ' . count(\$data) . ' escalas semanais importadas');
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
            DB::statement(\"SELECT setval('employee_work_shift_assignments_id_seq', ?, true)\", [\$maxId]);
        }
        
        \$this->command->info('  → ' . count(\$data) . ' atribuições importadas');
    }

    private function seedAdminUser(): void
    {
        \$this->command->info('Criando usuário Super Administrador...');
        
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@assai.pr.gov.br'],
            [
                'name' => 'Administrador',
                'email' => 'admin@assai.pr.gov.br',
                'password' => bcrypt('admin123'),
                'cpf' => '00000000000',
                'role' => 'admin',
                'is_active' => true,
                'is_super_admin' => true,
                'establishment_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        \$this->command->info('  → Super Admin criado (admin@assai.pr.gov.br / admin123)');
    }

    // ========================================
    // DADOS EXPORTADOS
    // ========================================

";

// Helper function to format value for PHP
function formatValue($value) {
    if (is_null($value)) return 'null';
    if (is_bool($value)) return $value ? 'true' : 'false';
    if (is_numeric($value) && !is_string($value)) return $value;
    return "'" . addslashes($value) . "'";
}

// Export Establishments
echo "Exportando establishments...\n";
$establishments = DB::table('establishments')->orderBy('id')->get();
$output .= "    private function getEstablishmentsData(): array\n    {\n        return [\n";
foreach ($establishments as $row) {
    $output .= "            ['id' => {$row->id}, 'corporate_name' => " . formatValue($row->corporate_name) . ", 'trade_name' => " . formatValue($row->trade_name) . ", 'cnpj' => " . formatValue($row->cnpj) . ", 'state_registration' => " . formatValue($row->state_registration) . ", 'street' => " . formatValue($row->street) . ", 'number' => " . formatValue($row->number) . ", 'complement' => " . formatValue($row->complement) . ", 'neighborhood' => " . formatValue($row->neighborhood) . ", 'city' => " . formatValue($row->city) . ", 'state' => " . formatValue($row->state) . ", 'zip_code' => " . formatValue($row->zip_code) . ", 'phone' => " . formatValue($row->phone) . ", 'email' => " . formatValue($row->email) . "],\n";
}
$output .= "        ];\n    }\n\n";

// Export Departments
echo "Exportando departments...\n";
$departments = DB::table('departments')->orderBy('id')->get();
$output .= "    private function getDepartmentsData(): array\n    {\n        return [\n";
foreach ($departments as $row) {
    $output .= "            ['id' => {$row->id}, 'establishment_id' => {$row->establishment_id}, 'name' => " . formatValue($row->name) . ", 'responsible' => " . formatValue($row->responsible) . "],\n";
}
$output .= "        ];\n    }\n\n";

// Export People
echo "Exportando people...\n";
$people = DB::table('people')->orderBy('id')->get();
$output .= "    private function getPeopleData(): array\n    {\n        return [\n";
foreach ($people as $row) {
    $output .= "            ['id' => {$row->id}, 'full_name' => " . formatValue($row->full_name) . ", 'cpf' => " . formatValue($row->cpf) . ", 'pis_pasep' => " . formatValue($row->pis_pasep) . ", 'ctps' => " . formatValue($row->ctps) . "],\n";
}
$output .= "        ];\n    }\n\n";

// Export Employee Registrations
echo "Exportando employee_registrations...\n";
$registrations = DB::table('employee_registrations')->orderBy('id')->get();
$output .= "    private function getEmployeeRegistrationsData(): array\n    {\n        return [\n";
foreach ($registrations as $row) {
    $status = formatValue($row->status);
    $admissionDate = formatValue($row->admission_date ?? '2020-01-01');
    $position = formatValue($row->position ?? null);
    $output .= "            ['id' => {$row->id}, 'person_id' => {$row->person_id}, 'establishment_id' => {$row->establishment_id}, 'department_id' => " . ($row->department_id ?? 'null') . ", 'matricula' => " . formatValue($row->matricula) . ", 'admission_date' => {$admissionDate}, 'position' => {$position}, 'status' => {$status}],\n";
}
$output .= "        ];\n    }\n\n";

// Export Work Shift Templates
echo "Exportando work_shift_templates...\n";
$templates = DB::table('work_shift_templates')->orderBy('id')->get();
$output .= "    private function getWorkShiftTemplatesData(): array\n    {\n        return [\n";
foreach ($templates as $row) {
    $output .= "            ['id' => {$row->id}, 'name' => " . formatValue($row->name) . ", 'description' => " . formatValue($row->description) . ", 'type' => " . formatValue($row->type) . ", 'weekly_hours' => " . ($row->weekly_hours ?? 'null') . ", 'is_preset' => " . ($row->is_preset ? 'true' : 'false') . ", 'created_by' => " . ($row->created_by ?? 'null') . ", 'calculation_mode' => " . formatValue($row->calculation_mode) . "],\n";
}
$output .= "        ];\n    }\n\n";

// Export Template Weekly Schedules
echo "Exportando template_weekly_schedules...\n";
$schedules = DB::table('template_weekly_schedules')->orderBy('id')->get();
$output .= "    private function getTemplateWeeklySchedulesData(): array\n    {\n        return [\n";
foreach ($schedules as $row) {
    $output .= "            ['id' => {$row->id}, 'template_id' => {$row->template_id}, 'day_of_week' => {$row->day_of_week}, 'entry_1' => " . formatValue($row->entry_1) . ", 'exit_1' => " . formatValue($row->exit_1) . ", 'entry_2' => " . formatValue($row->entry_2) . ", 'exit_2' => " . formatValue($row->exit_2) . ", 'entry_3' => " . formatValue($row->entry_3 ?? null) . ", 'exit_3' => " . formatValue($row->exit_3 ?? null) . ", 'is_work_day' => " . ($row->is_work_day ? 'true' : 'false') . ", 'daily_hours' => " . ($row->daily_hours ?? 'null') . "],\n";
}
$output .= "        ];\n    }\n\n";

// Export Employee Work Shift Assignments
echo "Exportando employee_work_shift_assignments...\n";
$assignments = DB::table('employee_work_shift_assignments')->orderBy('id')->get();
$output .= "    private function getEmployeeWorkShiftAssignmentsData(): array\n    {\n        return [\n";
foreach ($assignments as $row) {
    $output .= "            ['id' => {$row->id}, 'employee_registration_id' => {$row->employee_registration_id}, 'template_id' => {$row->template_id}, 'effective_from' => " . formatValue($row->effective_from) . ", 'assigned_by' => " . ($row->assigned_by ?? 'null') . ", 'assigned_at' => " . formatValue($row->assigned_at) . "],\n";
}
$output .= "        ];\n    }\n";

// Close class
$output .= "}\n";

// Write file
file_put_contents($outputFile, $output);

echo "\n✅ Arquivo gerado com sucesso: {$outputFile}\n";
echo "   Tamanho: " . number_format(filesize($outputFile) / 1024, 2) . " KB\n";
