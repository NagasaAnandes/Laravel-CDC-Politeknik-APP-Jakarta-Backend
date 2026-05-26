<?php

namespace App\Filament\Portal\Pages;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use App\Models\JobApplicationLog;
use App\Models\JobVacancy;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class JobDetail extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.portal.pages.job-detail';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'jobs/show';

    public JobVacancy $job;

    public ?JobApplication $myApplication = null;

    public ?TemporaryUploadedFile $cvFile = null;

    public string $note = '';

    protected function resolveJob(int $jobId): JobVacancy
    {
        /** @var JobVacancy $job */
        $job = JobVacancy::published()
            ->with(['company:id,name'])
            ->findOrFail($jobId);

        return $job;
    }

    public function mount(Request $request): void
    {
        $jobId = $request->integer('job');

        if ($jobId < 1) {
            abort(404);
        }

        $this->job = $this->resolveJob($jobId);
        $this->myApplication = $this->resolveMyApplication($jobId);
    }

    protected function resolveMyApplication(int $jobId): ?JobApplication
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return JobApplication::query()
            ->where('job_vacancy_id', $jobId)
            ->where('user_id', $user->id)
            ->latest('applied_at')
            ->first();
    }

    public function submitApplication(): void
    {
        Gate::authorize('create', JobApplication::class);

        if ($this->myApplication) {
            Notification::make()
                ->title('You already applied for this job')
                ->warning()
                ->send();

            return;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $rules = [
            'note' => ['nullable', 'string', 'max:2000'],
            'cvFile' => $user->hasCv()
                ? ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120']
                : ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];

        $validated = $this->validate($rules);

        if ($this->cvFile) {
            $cvPath = $this->storePrimaryCv($user);
            $user->forceFill(['cv_path' => $cvPath])->save();
        }

        $userAgent = substr((string) RequestFacade::userAgent(), 0, 255);
        $ipAddress = RequestFacade::ip();

        try {
            DB::transaction(function () use ($user, $validated, $userAgent, $ipAddress): void {
                $lockedJob = JobVacancy::whereKey($this->job->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedJob->isPublished()) {
                    abort(404);
                }

                JobApplication::create([
                    'job_vacancy_id' => $lockedJob->id,
                    'user_id' => $user->id,
                    'status' => JobApplicationStatus::PENDING,
                    'note' => $validated['note'] ?? null,
                    'applied_at' => now(),
                ]);

                JobApplicationLog::create([
                    'job_vacancy_id' => $lockedJob->id,
                    'user_id' => $user->id,
                    'event_type' => 'apply',
                    'clicked_at' => now(),
                    'user_agent' => $userAgent,
                    'ip_address' => $ipAddress,
                ]);
            });
        } catch (QueryException $exception) {
            if (! $this->isDuplicateApplicationException($exception)) {
                throw $exception;
            }

            Notification::make()
                ->title('You already applied for this job')
                ->warning()
                ->send();

            $this->myApplication = $this->resolveMyApplication($this->job->id);

            return;
        }

        $this->myApplication = $this->resolveMyApplication($this->job->id);
        $this->reset('cvFile', 'note');

        Notification::make()
            ->title('Application submitted successfully')
            ->success()
            ->send();
    }

    protected function storePrimaryCv(User $user): string
    {
        $directory = 'cvs/user-' . $user->id;
        $extension = strtolower((string) ($this->cvFile->extension() ?: 'pdf'));
        $filename = 'primary-cv-' . now()->format('YmdHis') . '.' . $extension;

        $oldCvPath = $user->cv_path;

        $path = Storage::disk('private')->putFileAs($directory, $this->cvFile, $filename);

        if ($oldCvPath && $oldCvPath !== $path) {
            Storage::disk('private')->delete($oldCvPath);
        }

        return $path;
    }

    protected function isDuplicateApplicationException(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'job_application_unique')
            || str_contains($message, 'Duplicate entry');
    }

    public function getTitle(): string
    {
        return 'Job Detail';
    }
}
