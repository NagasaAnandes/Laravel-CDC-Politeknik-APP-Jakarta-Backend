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

            // Polymorphic relation
            $table->morphs('approvable');
            // creates:
            // approvable_type (string)
            // approvable_id (unsignedBigInt)
            // + composite index

            $table->string('from_status')->nullable();
            $table->string('to_status');

            $table->string('action');
            // submit / approve / reject / cancel / auto_approve etc

            $table->foreignId('performed_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('reason')->nullable();

            $table->timestamps();

            // Extra indexes for production analytics
            $table->index('action');
            $table->index('to_status');
            $table->index(['approvable_type', 'to_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
