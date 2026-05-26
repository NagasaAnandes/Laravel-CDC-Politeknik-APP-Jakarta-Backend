<?php

namespace App\Filament\Portal\Widgets;

use App\Enums\JobApplicationStatus;
use App\Models\Event;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use Filament\Widgets\Widget;

class PortalExperienceWidget extends Widget
{
    protected static ?int $sort = 5;

    protected string $view = 'filament.portal.widgets.portal-experience';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isActive() === true
            && $user->isStudentOrAlumni();
    }

    protected function getViewData(): array
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $profileFields = [
            filled($user->name),
            filled($user->email),
            filled($user->phone),
            filled($user->linkedin_url),
            filled($user->graduation_year),
            filled($user->program_study),
            filled($user->cv_path),
        ];

        $profileCompletion = (int) round((array_sum($profileFields) / count($profileFields)) * 100);

        $latestJobs = JobVacancy::published()
            ->with(['company:id,name,logo_path'])
            ->latest('published_at')
            ->take(3)
            ->get();

        $latestEvents = Event::published()
            ->with(['company:id,name'])
            ->latest('published_at')
            ->take(3)
            ->get();

        $applicationsQuery = JobApplication::query()
            ->with(['jobVacancy.company:id,name'])
            ->forUser($user->id)
            ->latest('applied_at');

        $applications = [
            'total' => (clone $applicationsQuery)->count(),
            'pending' => (clone $applicationsQuery)->pending()->count(),
            'reviewed' => (clone $applicationsQuery)->reviewed()->count(),
            'accepted' => (clone $applicationsQuery)->accepted()->count(),
            'rejected' => (clone $applicationsQuery)->rejected()->count(),
        ];

        $latestApplication = (clone $applicationsQuery)->first();

        $missingProfileFields = collect([
            'Phone number' => filled($user->phone),
            'LinkedIn' => filled($user->linkedin_url),
            'Graduation year' => filled($user->graduation_year),
            'Program study' => filled($user->program_study),
            'CV uploaded' => filled($user->cv_path),
        ])
            ->filter(fn (bool $hasValue): bool => ! $hasValue)
            ->keys()
            ->values()
            ->all();

        return [
            'user' => $user,
            'profileCompletion' => $profileCompletion,
            'missingProfileFields' => $missingProfileFields,
            'latestJobs' => $latestJobs,
            'latestEvents' => $latestEvents,
            'applications' => $applications,
            'latestApplication' => $latestApplication,
        ];
    }
}
