<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcement_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('announcement_id')
                ->constrained('announcements')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('viewed_at');

            // Optional but useful for reporting
            $table->index(['announcement_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_views');
    }
};
