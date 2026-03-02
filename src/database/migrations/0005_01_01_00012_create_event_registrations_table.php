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

            $table->timestamp('registered_at');

            /*
            |--------------------------------------------------------------------------
            | CONSTRAINTS
            |--------------------------------------------------------------------------
            */

            // Prevent duplicate registration (atomic safety)
            $table->unique(
                ['event_id', 'user_id'],
                'event_user_unique_registration'
            );

            /*
            |--------------------------------------------------------------------------
            | INDEXING STRATEGY
            |--------------------------------------------------------------------------
            */

            // For event quota counting
            $table->index('event_id');

            // For user registration history
            $table->index(['user_id', 'registered_at']);

            // For reporting by time
            $table->index('registered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
