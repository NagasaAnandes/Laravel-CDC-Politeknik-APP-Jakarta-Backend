<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileCvTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_and_delete_cv(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

        $this->postJson('/api/v1/profile/cv', [
            'file' => $file,
        ])->assertStatus(201)
          ->assertJsonPath('data.has_cv', true)
          ->assertJsonStructure(['data' => ['cv_url']]);

        $this->assertTrue($user->fresh()->hasCv());

        // Download should return 200
        $this->get('/api/v1/profile/cv/download')->assertStatus(200);

        // Delete
        $this->deleteJson('/api/v1/profile/cv')->assertOk();

        $this->assertFalse($user->fresh()->hasCv());
    }
}
