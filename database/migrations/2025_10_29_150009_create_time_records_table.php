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
        Schema::create('time_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->dateTime('recorded_at');
            $table->date('record_date');
            $table->time('record_time');
            $table->string('nsr', 20)->nullable();
            $table->char('record_type', 1)->nullable();
            $table->boolean('imported_from_afd')->default(false);
            $table->string('afd_file_name')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'record_date']);
            $table->index('record_date');
            $table->index(['employee_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_records');
    }
};
