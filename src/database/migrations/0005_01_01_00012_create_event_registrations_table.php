<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELATIONS
            |--------------------------------------------------------------------------
            */

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | REGISTRATION DATA
            |--------------------------------------------------------------------------
            */

            // ✅ Auto timestamp (hindari null & human error)
            $table->timestamp('registered_at')->useCurrent();

            /*
            |--------------------------------------------------------------------------
            | SOFT DELETE (OPTIONAL BUT RECOMMENDED)
            |--------------------------------------------------------------------------
            */

            // ✅ Support cancel / re-register future
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | CONSTRAINTS
            |--------------------------------------------------------------------------
            */

            // ✅ Prevent duplicate active registration
            $table->unique(
                ['event_id', 'user_id', 'deleted_at'],
                'event_user_unique_registration'
            );

            /*
            |--------------------------------------------------------------------------
            | INDEXING STRATEGY
            |--------------------------------------------------------------------------
            */

            // For event lookup
            $table->index('event_id');

            // For user history
            $table->index(['user_id', 'registered_at']);

            // For reporting
            $table->index('registered_at');

            // ✅ Optimized for event analytics & sorting
            $table->index(['event_id', 'registered_at'], 'event_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
