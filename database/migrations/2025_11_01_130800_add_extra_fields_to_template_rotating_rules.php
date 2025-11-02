<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('template_rotating_rules', function (Blueprint $table) {
            $table->boolean('uses_cycle_pattern')
                ->default(true)
                ->after('shift_duration_hours')
                ->comment('Se usa padrão de ciclo (ex: 12x36, 24x72)');
            
            $table->boolean('validate_exact_hours')
                ->default(true)
                ->after('uses_cycle_pattern')
                ->comment('Se valida horas exatas ou apenas presença');
            
            $table->integer('tolerance_minutes')
                ->default(15)
                ->after('validate_exact_hours')
                ->comment('Tolerância em minutos para validação');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_rotating_rules', function (Blueprint $table) {
            $table->dropColumn(['uses_cycle_pattern', 'validate_exact_hours', 'tolerance_minutes']);
        });
    }
};
