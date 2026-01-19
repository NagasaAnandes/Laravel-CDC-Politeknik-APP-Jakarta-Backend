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

            $table->string('title', 150);
            $table->text('description');

            $table->enum('event_type', [
                'seminar',
                'bootcamp',
                'workshop',
            ]);

            $table->string('organizer', 100)->nullable();
            $table->string('location', 150)->nullable();

            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');

            $table->enum('registration_method', [
                'internal',
                'redirect',
            ]);

            $table->string('registration_url')->nullable();

            // NULL = unlimited quota
            $table->unsignedInteger('quota')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'published_at']);
            $table->index('event_type');
            $table->index('start_datetime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
