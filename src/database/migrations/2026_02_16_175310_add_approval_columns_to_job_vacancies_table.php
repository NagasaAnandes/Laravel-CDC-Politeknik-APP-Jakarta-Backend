<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {

            $table->enum('approval_status', [
                'draft',
                'pending',
                'approved',
                'rejected',
            ])->default('approved')->after('expired_at');

            $table->timestamp('submitted_at')->nullable()->after('approval_status');

            $table->timestamp('approved_at')->nullable()->after('submitted_at');
            $table->foreignId('approved_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')
                ->nullable()
                ->after('rejected_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejection_reason')->nullable()->after('rejected_by');

            // Index untuk public query
            $table->index('approval_status');
            $table->index(['approval_status', 'is_active', 'published_at'], 'job_public_visibility_index');
        });
    }

    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {

            $table->dropIndex(['approval_status']);
            $table->dropIndex('job_public_visibility_index');

            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);

            $table->dropColumn([
                'approval_status',
                'submitted_at',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'rejection_reason',
            ]);
        });
    }
};
