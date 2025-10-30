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
        Schema::create('template_weekly_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('work_shift_templates')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('0=Domingo, 1=Segunda, ..., 6=Sábado');
            $table->time('entry_1')->nullable();
            $table->time('exit_1')->nullable();
            $table->time('entry_2')->nullable();
            $table->time('exit_2')->nullable();
            $table->time('entry_3')->nullable();
            $table->time('exit_3')->nullable();
            $table->boolean('is_work_day')->default(true);
            $table->decimal('daily_hours', 4, 2)->nullable()->comment('Total de horas no dia');
            $table->timestamps();
            
            $table->unique(['template_id', 'day_of_week']);
            $table->index(['template_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_weekly_schedules');
    }
};
