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
        Schema::create('job_vacancies', function (Blueprint $table) {

            $table->id();

            /*
    |--------------------------------------------------------------------------
    | Optimistic Locking (IMPORTANT)
    |--------------------------------------------------------------------------
    */

            $table->unsignedInteger('version')
                ->default(0)
                ->index();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title', 150);
            $table->string('location', 100)->nullable();
            $table->string('employment_type', 50);
            $table->text('description');
            $table->string('external_apply_url', 255);
            $table->string('poster_path')->nullable();

            // Approval Workflow
            $table->string('approval_status', 20)->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejection_reason')->nullable();

            // Publication
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expired_at')->nullable()->index();

            $table->boolean('is_active')->default(false)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['approval_status', 'is_active', 'published_at'],
                'job_visibility_core_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
