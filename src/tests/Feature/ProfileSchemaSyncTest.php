<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileSchemaSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_contains_profile_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'phone',
            'linkedin_url',
            'graduation_year',
            'program_study',
        ]));
    }

    public function test_profile_update_persists_all_profile_fields(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $payload = [
            'phone' => '081234567890',
            'linkedin_url' => 'https://www.linkedin.com/in/example',
            'graduation_year' => 2024,
            'program_study' => 'Teknik Informatika',
        ];

        $this->putJson('/api/v1/profile', $payload)
            ->assertOk()
            ->assertJson(['message' => 'Profile updated successfully']);

        $this->assertDatabaseHas('users', array_merge([
            'id' => $user->id,
        ], $payload));

        $this->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.profile.phone', $payload['phone'])
            ->assertJsonPath('data.profile.linkedin_url', $payload['linkedin_url'])
            ->assertJsonPath('data.profile.graduation_year', $payload['graduation_year'])
            ->assertJsonPath('data.profile.program_study', $payload['program_study']);
    }

    public function test_phone_validation_matches_database_length(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile', [
            'phone' => str_repeat('1', 31),
        ])->assertStatus(422);
    }
}
