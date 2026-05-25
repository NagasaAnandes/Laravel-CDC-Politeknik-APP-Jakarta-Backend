<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'cv_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('cv_path')->nullable()->after('program_study');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'cv_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('cv_path');
            });
        }
    }
};
