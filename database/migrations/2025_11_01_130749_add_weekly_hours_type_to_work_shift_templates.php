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
        // Dropar a constraint CHECK existente e recriar com o novo valor
        DB::statement("
            ALTER TABLE work_shift_templates 
            DROP CONSTRAINT IF EXISTS work_shift_templates_type_check
        ");
        
        DB::statement("
            ALTER TABLE work_shift_templates
            ADD CONSTRAINT work_shift_templates_type_check 
            CHECK (type IN ('weekly', 'rotating_shift', 'weekly_hours'))
        ");
        
        // Adicionar coluna calculation_mode
        Schema::table('work_shift_templates', function (Blueprint $table) {
            $table->string('calculation_mode', 20)
                ->default('fixed_schedule')
                ->after('type')
                ->comment('fixed_schedule, rotating_cycle, flexible_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_shift_templates', function (Blueprint $table) {
            $table->dropColumn('calculation_mode');
        });
        
        // Nota: Não é possível remover valores de um ENUM no PostgreSQL facilmente
        // Seria necessário recriar o tipo, o que é complexo e pode quebrar dados existentes
    }
};
