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
        // Alterar campo record_type de char(1) para varchar(10)
        // para suportar tipos como 'O5', 'DP', 'HP', 'SF', etc.
        Schema::table('time_records', function (Blueprint $table) {
            $table->string('record_type', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_records', function (Blueprint $table) {
            $table->char('record_type', 1)->nullable()->change();
        });
    }
};
