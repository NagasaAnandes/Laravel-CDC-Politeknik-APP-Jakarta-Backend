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
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('company_name', 150);
            $table->string('location', 100)->nullable();
            $table->enum('employment_type', ['fulltime', 'parttime', 'intern', 'remote']);
            $table->text('description');
            $table->string('external_apply_url', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->timestamps();

            // Index
            $table->index(['is_active', 'published_at']);
            $table->index('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
