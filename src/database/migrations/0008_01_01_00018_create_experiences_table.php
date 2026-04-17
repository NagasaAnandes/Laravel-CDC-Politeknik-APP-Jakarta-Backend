<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();

            // FK
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Core fields
            $table->string('company_name', 150);
            $table->string('position', 150);
            $table->string('employment_type', 50);
            $table->string('location', 150)->nullable();

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index (WAJIB)
            $table->index('user_id', 'idx_experiences_user');
            $table->index(['user_id', 'is_current'], 'idx_experiences_user_current');
            $table->index(['start_date', 'end_date'], 'idx_experiences_dates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
