<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'pending_review' status to afd_imports table for the pending employees review feature.
     */
    public function up(): void
    {
        // Para PostgreSQL, precisamos adicionar o novo valor ao tipo enum
        // ou alterar a constraint de check
        
        // Primeiro, remover a constraint existente
        DB::statement("ALTER TABLE afd_imports DROP CONSTRAINT IF EXISTS afd_imports_status_check");
        
        // Adicionar nova constraint com o valor 'pending_review' incluído
        DB::statement("ALTER TABLE afd_imports ADD CONSTRAINT afd_imports_status_check CHECK (status::text = ANY (ARRAY['processing'::text, 'completed'::text, 'failed'::text, 'pending_review'::text]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover constraint atual
        DB::statement("ALTER TABLE afd_imports DROP CONSTRAINT IF EXISTS afd_imports_status_check");
        
        // Restaurar constraint original (sem pending_review)
        DB::statement("ALTER TABLE afd_imports ADD CONSTRAINT afd_imports_status_check CHECK (status::text = ANY (ARRAY['processing'::text, 'completed'::text, 'failed'::text]))");
    }
};
