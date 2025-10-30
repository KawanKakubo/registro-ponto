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
            if (!Schema::hasColumn('afd_imports', 'records_imported')) {
                $table->integer('records_imported')->default(0)->after('total_records');
            }
            if (!Schema::hasColumn('afd_imports', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('error_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afd_imports', function (Blueprint $table) {
            $table->dropColumn(['records_imported', 'processed_at']);
        });
    }
};
