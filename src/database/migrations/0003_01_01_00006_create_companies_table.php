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

            $table->string('email_contact', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();

            $table->string('logo_path')->nullable();

            /*
    |--------------------------------------------------------------------------
    | Partnership
    |--------------------------------------------------------------------------
    */

            $table->boolean('is_partner')->default(false);

            /*
    |--------------------------------------------------------------------------
    | Activation
    |--------------------------------------------------------------------------
    */

            $table->boolean('is_active')->default(true);

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_partner', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
