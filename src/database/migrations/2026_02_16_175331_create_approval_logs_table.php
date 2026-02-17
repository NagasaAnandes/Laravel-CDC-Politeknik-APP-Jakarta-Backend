<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();

            $table->morphs('approvable');

            $table->string('from_status', 20);
            $table->string('to_status', 20);
            $table->string('action', 50);

            $table->foreignId('performed_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
