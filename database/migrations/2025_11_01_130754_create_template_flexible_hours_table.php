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
        Schema::create('template_flexible_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')
                ->constrained('work_shift_templates')
                ->onDelete('cascade');
            
            $table->decimal('weekly_hours_required', 5, 2)
                ->comment('Carga horária semanal exigida (ex: 20h, 30h)');
            
            $table->enum('period_type', ['weekly', 'biweekly', 'monthly'])
                ->default('weekly')
                ->comment('Período de apuração');
            
            $table->integer('grace_minutes')
                ->default(0)
                ->comment('Tolerância em minutos para considerar falta');
            
            $table->boolean('requires_minimum_daily_hours')
                ->default(false)
                ->comment('Se exige mínimo de horas por dia trabalhado');
            
            $table->decimal('minimum_daily_hours', 4, 2)
                ->nullable()
                ->comment('Mínimo de horas por dia (se aplicável)');
            
            $table->integer('minimum_days_per_week')
                ->nullable()
                ->comment('Mínimo de dias por semana (opcional)');
            
            $table->timestamps();
            
            $table->unique('template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_flexible_hours');
    }
};
