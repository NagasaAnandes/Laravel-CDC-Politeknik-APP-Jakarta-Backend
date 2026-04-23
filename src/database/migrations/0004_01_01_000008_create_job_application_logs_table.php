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

            $table->foreignId('job_vacancy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // 🔥 Tracking core
            $table->string('session_id')->nullable()->index();
            $table->string('event_type', 20)->index(); // click, apply

            // 🔥 Standard timestamps (IMPORTANT)
            $table->timestamps();

            // 🔍 Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // 🚀 Optimized indexes
            $table->index(['job_vacancy_id', 'event_type', 'created_at'], 'job_event_time_index');
            $table->index(['event_type', 'created_at'], 'event_time_index');
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
