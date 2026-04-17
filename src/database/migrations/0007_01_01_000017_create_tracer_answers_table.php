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
        Schema::create('tracer_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('response_id')
                ->constrained('tracer_responses')
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->constrained('tracer_questions')
                ->cascadeOnDelete();

            $table->text('answer_value')->nullable();

            $table->json('answer_json')->nullable();

            $table->timestamps();

            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracer_answers');
    }
};
