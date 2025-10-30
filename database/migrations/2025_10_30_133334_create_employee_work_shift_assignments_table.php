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
        Schema::create('employee_work_shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->constrained('work_shift_templates')->onDelete('restrict');
            $table->date('cycle_start_date')->nullable()->comment('Data de início do ciclo (para rotating_shift)');
            $table->date('effective_from')->comment('Data inicial da vigência');
            $table->date('effective_until')->nullable()->comment('Data final da vigência (NULL = sem fim)');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['employee_id', 'effective_from', 'effective_until']);
            $table->index('template_id');
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_work_shift_assignments');
    }
};
