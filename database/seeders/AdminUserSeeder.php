<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Administrador',
            'cpf' => '00000000000',
            'email' => 'admin@assai.pr.gov.br',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->command->info('Usuário administrador criado com sucesso!');
        $this->command->info('CPF: 000.000.000-00');
        $this->command->info('Senha: admin123');
    }
}
