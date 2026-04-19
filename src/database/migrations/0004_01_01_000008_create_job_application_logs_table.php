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
        Schema::create('job_application_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('session_id')->nullable();
            $table->string('event_type', 20); // click, apply

            $table->timestamp('clicked_at')->useCurrent();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Indexing
            $table->index(['job_vacancy_id', 'clicked_at']);
            $table->index('user_id');
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_application_logs');
    }
};
