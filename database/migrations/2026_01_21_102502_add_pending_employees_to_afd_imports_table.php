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
        Schema::table('afd_imports', function (Blueprint $table) {
            // JSON com colaboradores não encontrados
            // Formato: [{ "key": "matricula_123", "matricula": "123", "pis": null, "cpf": null, "records_count": 45, "first_record": "2025-01-01 08:00:00" }]
            $table->json('pending_employees')->nullable()->after('error_message');
            
            // JSON com registros pendentes (batidas não importadas)
            // Formato: [{ "employee_key": "matricula_123", "recorded_at": "2025-01-01 08:00:00", "nsr": "000000001", "record_type": "O5" }]
            $table->json('pending_records')->nullable()->after('pending_employees');
            
            // Contador de colaboradores pendentes
            $table->integer('pending_count')->default(0)->after('pending_records');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afd_imports', function (Blueprint $table) {
            $table->dropColumn(['pending_employees', 'pending_records', 'pending_count']);
        });
    }
};
