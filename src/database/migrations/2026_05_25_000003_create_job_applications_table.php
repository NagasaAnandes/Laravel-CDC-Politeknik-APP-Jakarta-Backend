<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_vacancy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status', 20)->default('pending')->index();
            $table->text('note')->nullable();

            $table->timestamp('applied_at')->useCurrent()->index();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['job_vacancy_id', 'user_id'], 'job_application_unique');
            $table->index(['job_vacancy_id', 'status', 'applied_at'], 'job_application_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
