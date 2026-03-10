<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Company;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Super Admin',
            'email' => 'super@cdc.test',
            'password' => Hash::make('password'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | ADMIN CDC
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Admin CDC',
            'email' => 'admin@cdc.test',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN_CDC,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | COMPANIES + COMPANY USERS (1:1)
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 2; $i++) {

            $company = Company::create([
                'name' => "Company $i",
                'industry' => 'Technology',
                'email_contact' => "company$i@test.com",
                'is_active' => true,
            ]);

            User::create([
                'name' => "Company User $i",
                'email' => "company$i@cdc.test",
                'password' => Hash::make('password'),
                'role' => UserRole::COMPANY,
                'company_id' => $company->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENTS
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 2; $i++) {
            User::create([
                'name' => "Student $i",
                'email' => "student$i@cdc.test",
                'password' => Hash::make('password'),
                'role' => UserRole::STUDENT,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ALUMNI
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 2; $i++) {
            User::create([
                'name' => "Alumni $i",
                'email' => "alumni$i@cdc.test",
                'password' => Hash::make('password'),
                'role' => UserRole::ALUMNI,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
