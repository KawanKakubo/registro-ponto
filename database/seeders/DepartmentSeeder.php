<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa a tabela com CASCADE para PostgreSQL
        DB::statement('TRUNCATE TABLE departments RESTART IDENTITY CASCADE;');

        $departments = [
            ['id' => 1, 'name' => 'ADMINISTRAÇÃO E RH'],
            ['id' => 2, 'name' => 'AGRICULTURA E ABASTECIMENTO'],
            ['id' => 3, 'name' => 'AGRICULTURA, ABASTECIMENTO E MEIO AMBIENTE'],
            ['id' => 4, 'name' => 'ASSISTÊNCIA SOCIAL'],
            ['id' => 5, 'name' => 'CIÊNCIA, TECNOLOGIA E INOVAÇÃO'],
            ['id' => 6, 'name' => 'CULTURA E TURISMO'],
            ['id' => 7, 'name' => 'DEFESA CIVIL'],
            ['id' => 8, 'name' => 'EDUCAÇÃO'],
            ['id' => 9, 'name' => 'ESPORTE E LAZER'],
            ['id' => 10, 'name' => 'FINANÇAS'],
            ['id' => 11, 'name' => 'GABINETE DO PREFEITO'],
            ['id' => 12, 'name' => 'NENHUM'],
            ['id' => 13, 'name' => 'OBRAS E SERVIÇOS URBANOS'],
            ['id' => 14, 'name' => 'PROCURADORIA MUNICIPAL'],
            ['id' => 15, 'name' => 'PÓLO UAB'],
            ['id' => 16, 'name' => 'SAÚDE'],
            ['id' => 17, 'name' => 'SEGURANÇA ALIMENTAR E NUTRICIONAL'],
            ['id' => 18, 'name' => 'TRABALHO E GERAÇÃO DE EMPREGOS'],
        ];

        foreach ($departments as $department) {
            Department::create([
                'id' => $department['id'],
                'establishment_id' => 1, // Prefeitura de Assaí
                'name' => $department['name'],
                'responsible' => null,
            ]);
        }

        $this->command->info('✅ ' . count($departments) . ' departamentos criados com sucesso!');
    }
}
