<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership (optional partner support)
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Core Content
            |--------------------------------------------------------------------------
            */

            $table->string('title', 150);
            $table->text('description');

            // Jangan pakai ENUM MySQL untuk fleksibilitas production
            $table->string('event_type');

            $table->string('organizer', 100)->nullable();
            $table->string('location', 150)->nullable();

            $table->date('registration_deadline');

            $table->string('registration_method'); // internal / redirect
            $table->string('registration_url')->nullable();

            $table->string('poster_path')->nullable();

            // NULL = unlimited
            $table->unsignedInteger('quota')->nullable();

            // Atomic counter for race-condition-safe quota control
            $table->unsignedInteger('registrations_count')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Approval Workflow
            |--------------------------------------------------------------------------
            */

            $table->string('approval_status')->default('draft');

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

            // Event specific lifecycle
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Publication Layer
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(false);
            $table->timestamp('published_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexing Strategy (Optimized for Public Query)
            |--------------------------------------------------------------------------
            */

            $table->index('company_id');

            $table->index('approval_status');

            $table->index([
                'approval_status',
                'is_active',
                'published_at',
                'registration_deadline'
            ], 'event_visibility_index');

            $table->index('registration_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
