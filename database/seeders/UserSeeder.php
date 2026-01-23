<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Establishment;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $establishment = Establishment::first();

        User::updateOrCreate(
            ['email' => 'admin@assai.pr.gov.br'],
            [
                'name' => 'Administrador',
                'cpf' => '00000000000',
                'password' => Hash::make('admin123'),
                'is_active' => true,
                'role' => 'admin',
                'establishment_id' => $establishment?->id,
            ]
        );
    }
}
