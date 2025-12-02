<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove a constraint antiga 'employees_cpf_unique' e cria uma nova
     * que permite múltiplos valores NULL (usando índice parcial no PostgreSQL)
     */
    public function up(): void
    {
        // Remover a constraint única antiga (que não permite múltiplos NULL)
        DB::statement('ALTER TABLE people DROP CONSTRAINT IF EXISTS employees_cpf_unique');
        
        // Criar um índice único parcial que permite múltiplos NULL
        // Este índice só aplica a restrição quando cpf IS NOT NULL
        DB::statement('CREATE UNIQUE INDEX people_cpf_unique ON people (cpf) WHERE cpf IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover o índice parcial
        DB::statement('DROP INDEX IF EXISTS people_cpf_unique');
        
        // Recriar a constraint única antiga
        DB::statement('ALTER TABLE people ADD CONSTRAINT employees_cpf_unique UNIQUE (cpf)');
    }
};
