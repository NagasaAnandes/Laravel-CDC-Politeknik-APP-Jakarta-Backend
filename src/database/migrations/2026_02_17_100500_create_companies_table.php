<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('slug', 160)->unique();

            $table->string('industry')->nullable();
            $table->string('website')->nullable();

            $table->string('email_contact', 150);
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();

            $table->string('logo_path')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
