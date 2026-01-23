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
        Schema::table('employee_work_shift_assignments', function (Blueprint $table) {
            $table->json('custom_settings')
                ->nullable()
                ->after('assigned_at')
                ->comment('Configurações personalizadas por colaborador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_work_shift_assignments', function (Blueprint $table) {
            $table->dropColumn('custom_settings');
        });
    }
};
