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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0=Domingo, 1=Segunda, ..., 6=Sábado
            $table->time('entry_1')->nullable();
            $table->time('exit_1')->nullable();
            $table->time('entry_2')->nullable();
            $table->time('exit_2')->nullable();
            $table->time('entry_3')->nullable();
            $table->time('exit_3')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
