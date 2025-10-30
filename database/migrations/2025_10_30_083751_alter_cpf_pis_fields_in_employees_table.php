<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primeiro, limpar dados existentes removendo formatação
        DB::statement("UPDATE employees SET cpf = regexp_replace(cpf, '[^0-9]', '', 'g')");
        DB::statement("UPDATE employees SET pis_pasep = regexp_replace(pis_pasep, '[^0-9]', '', 'g') WHERE pis_pasep IS NOT NULL");
        
        // Depois, alterar o tamanho das colunas (sem unique pois já existe)
        DB::statement("ALTER TABLE employees ALTER COLUMN cpf TYPE VARCHAR(11)");
        DB::statement("ALTER TABLE employees ALTER COLUMN pis_pasep TYPE VARCHAR(11)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE employees ALTER COLUMN cpf TYPE VARCHAR(14)");
        DB::statement("ALTER TABLE employees ALTER COLUMN pis_pasep TYPE VARCHAR(15)");
    }
};
