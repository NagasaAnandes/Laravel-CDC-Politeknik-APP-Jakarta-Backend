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
        Schema::create('tracer_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('survey_id')
                ->constrained('tracer_surveys')
                ->cascadeOnDelete();

            $table->string('section')->nullable();

            $table->text('question_text');

            $table->enum('type', [
                'text',
                'textarea',
                'radio',
                'checkbox',
                'select',
                'scale',
                'number'
            ]);

            $table->json('options')->nullable();

            $table->integer('scale_min')->nullable();
            $table->integer('scale_max')->nullable();

            $table->boolean('is_required')->default(false);

            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracer_questions');
    }
};
