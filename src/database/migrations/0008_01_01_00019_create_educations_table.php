<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();

            // FK
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('institution', 150);
            $table->string('degree', 100)->nullable();
            $table->string('field_of_study', 150)->nullable();

            $table->year('start_year');
            $table->year('end_year')->nullable();
            $table->boolean('is_current')->default(false);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('user_id', 'idx_educations_user');
            $table->index(['start_year', 'end_year'], 'idx_educations_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
