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

        User::create([
            'name' => 'Administrador',
            'email' => 'admin@assai.pr.gov.br',
            'password' => Hash::make('senha123'),
            'establishment_id' => $establishment?->id,
        ]);
    }
}
