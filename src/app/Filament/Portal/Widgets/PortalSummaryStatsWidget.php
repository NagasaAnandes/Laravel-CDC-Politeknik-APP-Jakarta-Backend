<?php

namespace App\Filament\Portal\Widgets;

use App\Enums\JobApplicationStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PortalSummaryStatsWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isActive() === true
            && $user->isStudentOrAlumni();
    }

    protected function getStats(): array
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

        $publishedJobsLast7Days = JobVacancy::published()
            ->where('published_at', '>=', now()->subDays(7))
            ->count();

        $publishedEventsLast7Days = Event::published()
            ->where('published_at', '>=', now()->subDays(7))
            ->count();

        $userApplications = JobApplication::query()
            ->with(['reviewedBy:id,name', 'jobVacancy:id,title'])
            ->forUser($user->id);

        $applyHistoryCount = (clone $userApplications)->count();
        $pendingApplications = (clone $userApplications)->pending()->count();
        $reviewedApplications = (clone $userApplications)->reviewed()->count();
        $acceptedApplications = (clone $userApplications)->accepted()->count();
        $rejectedApplications = (clone $userApplications)->rejected()->count();

        $latestApplication = (clone $userApplications)
            ->latest('applied_at')
            ->first();

        $eventRegistrationsCount = EventRegistration::query()
            ->where('user_id', $user->id)
            ->count();

        return [
            Stat::make('Profile Completion', $profileCompletion . '%')
                ->description('Basic profile readiness')
                ->color($profileCompletion >= 80 ? 'success' : 'warning'),

            Stat::make('New Jobs (7d)', $publishedJobsLast7Days)
                ->description('Recently published jobs')
                ->color('primary'),

            Stat::make('New Events (7d)', $publishedEventsLast7Days)
                ->description('Recently published events')
                ->color('info'),

            Stat::make('Applications', $applyHistoryCount)
                ->description($latestApplication
                    ? 'Latest: ' . $latestApplication->status?->label() . ' • ' . optional($latestApplication->applied_at)->format('d M Y')
                    : 'No applications yet')
                ->url(url('/portal/applications'))
                ->color($latestApplication?->status?->isFinal() ? 'success' : 'warning'),

            Stat::make('Latest Status', $latestApplication?->status?->label() ?? 'No applications')
                ->description($latestApplication
                    ? trim('On ' . ($latestApplication->jobVacancy?->title ?? 'Unknown job') . ' • Applied at ' . optional($latestApplication->applied_at)->format('d M Y H:i'))
                    : 'Track your newest application here')
                ->url(url('/portal/applications'))
                ->color($latestApplication?->status?->dashboardColor() ?? 'gray'),

            Stat::make('Pending', $pendingApplications)
                ->description('Awaiting review')
                ->color('warning'),

            Stat::make('Reviewed', $reviewedApplications)
                ->description('In review queue')
                ->color('info'),

            Stat::make('Accepted', $acceptedApplications)
                ->description('Positive outcomes')
                ->color('success'),

            Stat::make('Rejected', $rejectedApplications)
                ->description('Closed applications')
                ->color('danger'),

            Stat::make('Event Registrations', $eventRegistrationsCount)
                ->description('Registered events')
                ->color('warning'),
        ];
    }
}
