<?php

namespace Tests\Feature\Portal;

use App\Enums\ApprovalStatus;
use App\Enums\JobApplicationStatus;
use App\Enums\UserRole;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\JobApplication;
use App\Models\JobApplicationLog;
use App\Models\User;
use Carbon\Carbon;

class JobsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_jobs_page_displays_jobs_pagination_and_query_persistence()
    {
        $this->actingAs($this->makePortalUser());

        $company = Company::create(['name' => 'Acme Corp']);

        // create 13 published jobs
        for ($i = 1; $i <= 13; $i++) {
            $job = JobVacancy::create([
                'company_id' => $company->id,
                'title' => "Job Title #{$i}",
                'location' => 'Jakarta',
                'employment_type' => 'fulltime',
                'description' => 'Description here',
                'external_apply_url' => 'https://example.com/apply',
            ]);

            $job->forceFill([
                'approval_status' => 'approved',
                'is_active' => true,
                'published_at' => Carbon::now()->subDay(),
            ])->save();
        }

        $response = $this->get('/portal/jobs?search=Job&employment_type=fulltime');
        $response->assertStatus(200);
        $response->assertSee('Job Title #1');
        $response->assertSee('jobs found');
        $response->assertSee('data-company-avatar-fallback', false);
        $this->assertMatchesRegularExpression('/\/portal\/jobs\?(?:[^"\']*search=Job[^"\']*employment_type=fulltime|[^"\']*employment_type=fulltime[^"\']*search=Job)/', $response->getContent());

        // pagination: default per-page 12 -> should have page 1 with 12 and page 2 with remaining
        $this->assertStringContainsString('pagination', $response->getContent());
    }

    public function test_portal_jobs_page_marks_applied_jobs()
    {
        $user = $this->makePortalUser();
        $this->actingAs($user);

        $company = Company::create(['name' => 'Nova Vision Labs']);

        $job = JobVacancy::create([
            'company_id' => $company->id,
            'title' => 'Applied Job',
            'location' => 'Jakarta',
            'employment_type' => 'remote',
            'description' => 'Description here',
            'external_apply_url' => 'https://example.com/apply',
        ]);

        $job->forceFill([
            'approval_status' => ApprovalStatus::APPROVED->value,
            'is_active' => true,
            'published_at' => Carbon::now()->subDay(),
        ])->save();

        JobApplication::create([
            'job_vacancy_id' => $job->id,
            'user_id' => $user->id,
            'status' => JobApplicationStatus::PENDING,
            'applied_at' => now(),
        ]);

        $response = $this->get('/portal/jobs');
        $response->assertStatus(200);
        $response->assertSee('Application already submitted');
        $response->assertSee('Applied');
    }

    public function test_portal_jobs_page_shows_empty_state_when_filters_match_nothing()
    {
        $this->actingAs($this->makePortalUser());

        $company = Company::create(['name' => 'Acme Corp']);

        $job = JobVacancy::create([
            'company_id' => $company->id,
            'title' => 'Visible Job',
            'location' => 'Bandung',
            'employment_type' => 'fulltime',
            'description' => 'Description here',
            'external_apply_url' => 'https://example.com/apply',
        ]);

        $job->forceFill([
            'approval_status' => ApprovalStatus::APPROVED->value,
            'is_active' => true,
            'published_at' => Carbon::now()->subDay(),
        ])->save();

        $response = $this->get('/portal/jobs?search=NoMatch');
        $response->assertStatus(200);
        $response->assertSee('No jobs found');
        $response->assertSee('Reset search');
    }

    public function test_apply_endpoint_logs_and_returns_redirect()
    {
        $company = Company::create(['name' => 'Acme Corp']);

        $job = JobVacancy::create([
            'company_id' => $company->id,
            'title' => 'Apply Test',
            'location' => 'Bandung',
            'employment_type' => 'parttime',
            'description' => 'Description',
            'external_apply_url' => 'https://example.com/apply-here',
        ]);

        $job->forceFill([
            'approval_status' => ApprovalStatus::APPROVED->value,
            'is_active' => true,
            'published_at' => Carbon::now()->subDay(),
        ])->save();

        $response = $this->postJson('/api/jobs/' . $job->id . '/apply');
        $response->assertStatus(200);
        $response->assertJsonStructure(['redirect_url']);
        $this->assertDatabaseHas('job_application_logs', [
            'job_vacancy_id' => $job->id,
        ]);
    }

    protected function makePortalUser(): User
    {
        return User::factory()->create([
            'role' => UserRole::STUDENT,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
