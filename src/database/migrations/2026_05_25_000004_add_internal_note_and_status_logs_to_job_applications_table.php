<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('job_applications', 'internal_note')) {
            Schema::table('job_applications', function (Blueprint $table) {
                $table->text('internal_note')->nullable()->after('note');
            });
        }

        Schema::create('job_application_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_application_id')
                ->constrained('job_applications')
                ->cascadeOnDelete();

            $table->string('from_status', 20)->nullable()->index();
            $table->string('to_status', 20)->index();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['job_application_id', 'created_at'], 'job_application_status_history_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_status_logs');

        if (Schema::hasColumn('job_applications', 'internal_note')) {
            Schema::table('job_applications', function (Blueprint $table) {
                $table->dropColumn('internal_note');
            });
        }
    }
};
