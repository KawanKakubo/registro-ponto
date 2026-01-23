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
        // Tornar a coluna CPF nullable para permitir importação de dados legados
        DB::statement('ALTER TABLE people ALTER COLUMN cpf DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para NOT NULL (apenas se não houver registros com CPF NULL)
        DB::statement('ALTER TABLE people ALTER COLUMN cpf SET NOT NULL');
    }
};
