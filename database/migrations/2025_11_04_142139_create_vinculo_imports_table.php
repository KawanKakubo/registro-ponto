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
        Schema::create('vinculo_imports', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('csv_path');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('total_linhas')->default(0);
            $table->integer('pessoas_criadas')->default(0);
            $table->integer('pessoas_atualizadas')->default(0);
            $table->integer('vinculos_criados')->default(0);
            $table->integer('vinculos_atualizados')->default(0);
            $table->integer('jornadas_associadas')->default(0);
            $table->integer('templates_criados')->default(0);
            $table->integer('erros')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vinculo_imports');
    }
};
