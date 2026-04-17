<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            // FK
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 150);
            $table->string('issuer', 150)->nullable();

            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            // File metadata (IMPORTANT)
            $table->string('file_path', 255);
            $table->unsignedInteger('file_size');
            $table->string('file_mime', 100);

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('user_id', 'idx_certificates_user');
            $table->index('issue_date', 'idx_certificates_issue_date');

            // Anti-duplicate (optional but recommended)
            $table->unique(
                ['user_id', 'name', 'issuer'],
                'uniq_cert_user_name_issuer'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
