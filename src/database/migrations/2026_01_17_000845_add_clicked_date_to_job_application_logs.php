<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // STEP 1: add as nullable
        Schema::table('job_application_logs', function (Blueprint $table) {
            $table->date('clicked_date')
                ->nullable()
                ->after('clicked_at')
                ->index();
        });

        // STEP 2: backfill from clicked_at
        DB::table('job_application_logs')
            ->whereNull('clicked_date')
            ->update([
                'clicked_date' => DB::raw('DATE(clicked_at)')
            ]);

        // STEP 3: make it NOT NULL
        Schema::table('job_application_logs', function (Blueprint $table) {
            $table->date('clicked_date')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_application_logs', function (Blueprint $table) {
            $table->dropIndex(['clicked_date']);
            $table->dropColumn('clicked_date');
        });
    }
};
