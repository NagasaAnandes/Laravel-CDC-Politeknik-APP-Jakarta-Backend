<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('event_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete(); // ❗ jangan cascade

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('action', 20);

            // ✅ auto timestamp
            $table->timestamp('created_at')->useCurrent();

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL ANALYTICS
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('event_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');

            // ✅ penting untuk analytics
            $table->index(['event_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
