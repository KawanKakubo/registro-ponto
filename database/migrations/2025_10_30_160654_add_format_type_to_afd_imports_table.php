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
            $table->string('format_type', 50)->nullable()->after('file_path');
            $table->text('format_hint')->nullable()->after('format_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afd_imports', function (Blueprint $table) {
            $table->dropColumn(['format_type', 'format_hint']);
        });
    }
};
