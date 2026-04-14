<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
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
            $table->string('event_type');

            $table->string('organizer', 100)->nullable();
            $table->string('location', 150)->nullable();

            $table->date('registration_deadline');

            $table->string('registration_method');
            $table->string('registration_url')->nullable();

            $table->string('poster_path')->nullable();

            $table->unsignedInteger('quota')->nullable();
            $table->unsignedInteger('registrations_count')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Approval Workflow
            |--------------------------------------------------------------------------
            */

            $table->string('approval_status', 20)->default('draft');

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

            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Publication
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

            $table->unsignedInteger('version')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Indexing
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
            $table->index(['is_active', 'registration_deadline'], 'event_active_deadline_index');
        });

        /*
        |--------------------------------------------------------------------------
        | CHECK CONSTRAINTS (SAFE VERSION ONLY)
        |--------------------------------------------------------------------------
        */

        // ✅ ENUM STATUS ONLY (AMAN)
        DB::statement("
            ALTER TABLE events
            ADD CONSTRAINT check_events_approval_status
            CHECK (approval_status IN ('draft','submitted','approved','rejected','cancelled'))
        ");

        // ✅ REGISTRATION METHOD ENUM
        DB::statement("
            ALTER TABLE events
            ADD CONSTRAINT check_events_registration_method
            CHECK (registration_method IN ('internal','redirect'))
        ");

        // ✅ QUOTA >= 0
        DB::statement("
            ALTER TABLE events
            ADD CONSTRAINT check_events_quota
            CHECK (quota IS NULL OR quota >= 0)
        ");

        // ✅ REGISTRATION COUNT >= 0
        DB::statement("
            ALTER TABLE events
            ADD CONSTRAINT check_events_registration_count
            CHECK (registrations_count >= 0)
        ");

        // ⚠️ OPTIONAL (AMAN, TIDAK TIMING SENSITIVE)
        DB::statement("
            ALTER TABLE events
            ADD CONSTRAINT check_events_publication
            CHECK (
                (is_active = true AND published_at IS NOT NULL)
                OR
                (is_active = false AND published_at IS NULL)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
