<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'linkedin_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('linkedin_url')->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'graduation_year')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedSmallInteger('graduation_year')->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'program_study')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('program_study')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('users', 'linkedin_url')) {
                $columnsToDrop[] = 'linkedin_url';
            }

            if (Schema::hasColumn('users', 'graduation_year')) {
                $columnsToDrop[] = 'graduation_year';
            }

            if (Schema::hasColumn('users', 'program_study')) {
                $columnsToDrop[] = 'program_study';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
