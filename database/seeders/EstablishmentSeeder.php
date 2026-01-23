<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Establishment;

class EstablishmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Establishment::create([
            'id' => 1,
            'corporate_name' => 'Prefeitura Municipal de Assaí',
            'trade_name' => 'Prefeitura de Assaí',
            'cnpj' => '76.222.769/0001-37',
            'street' => 'Rua Exemplo',
            'number' => '100',
            'neighborhood' => 'Centro',
            'city' => 'Assaí',
            'state' => 'PR',
            'zip_code' => '86130-000',
            'phone' => '(43) 3262-1234',
            'email' => 'contato@assai.pr.gov.br',
        ]);
    }
}
