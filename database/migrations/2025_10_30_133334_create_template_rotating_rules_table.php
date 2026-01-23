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
        Schema::create('template_rotating_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('work_shift_templates')->onDelete('cascade');
            $table->integer('work_days')->comment('Dias de trabalho no ciclo');
            $table->integer('rest_days')->comment('Dias de descanso no ciclo');
            $table->time('shift_start_time')->nullable()->comment('Horário de início do turno');
            $table->time('shift_end_time')->nullable()->comment('Horário de fim do turno');
            $table->decimal('shift_duration_hours', 4, 2)->nullable()->comment('Duração do turno em horas');
            $table->timestamps();
            
            $table->unique('template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_rotating_rules');
    }
};
