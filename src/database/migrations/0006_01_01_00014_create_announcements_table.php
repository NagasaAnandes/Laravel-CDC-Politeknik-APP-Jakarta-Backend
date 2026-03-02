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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('content');

            $table->enum('category', ['career', 'academic', 'event', 'general']);
            $table->enum('priority', ['normal', 'important', 'urgent']);
            $table->enum('target_audience', ['student', 'alumni', 'all']);

            $table->string('redirect_url')->nullable();

            $table->boolean('is_active')->default(false);

            $table->timestamp('published_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(['is_active', 'published_at', 'expired_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
