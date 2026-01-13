<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('linkedin_url')->nullable()->after('phone');
            $table->integer('graduation_year')->nullable()->after('linkedin_url');
            $table->string('program_study')->nullable()->after('graduation_year');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'linkedin_url',
                'graduation_year',
                'program_study',
            ]);
        });
    }
};
