<?php

namespace App\Filament\Admin\Widgets;

use App\Models\JobVacancy;
use App\Models\JobApplicationLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobClickStats extends BaseWidget
{
    protected static ?int $sort = 20;

    protected function getStats(): array
    {
        $last30 = now()->subDays(30);

        /*
        |--------------------------------------------------------------------------
        | Jobs
        |--------------------------------------------------------------------------
        */

        $totalJobs = JobVacancy::count();
        $publishedJobs = JobVacancy::published()->count();

        /*
        |--------------------------------------------------------------------------
        | Click Metrics (LEAN)
        |--------------------------------------------------------------------------
        */

        $totalClicks = JobApplicationLog::where('event_type', 'click')->count();

        $clicksLast30 = JobApplicationLog::where('event_type', 'click')
            ->where('created_at', '>=', $last30)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Unique Users (Important but simple)
        |--------------------------------------------------------------------------
        */

        $uniqueUsers = JobApplicationLog::where('event_type', 'click')
            ->whereNotNull('session_id')
            ->distinct('session_id')
            ->count('session_id');

        /*
        |--------------------------------------------------------------------------
        | Apply (optional but useful)
        |--------------------------------------------------------------------------
        */

        $totalApply = JobApplicationLog::where('event_type', 'apply')->count();

        return [
            Stat::make('Total Jobs', $totalJobs)
                ->description('All time')
                ->color('primary'),

            Stat::make('Active Jobs', $publishedJobs)
                ->description('Currently published')
                ->color('success'),

            Stat::make('Total Clicks', $totalClicks)
                ->description('All time')
                ->color('info'),

            Stat::make('Clicks (30d)', $clicksLast30)
                ->description('Last 30 days')
                ->color('info'),

            Stat::make('Unique Visitors', $uniqueUsers)
                ->description('Approx. real users')
                ->color('warning'),

            Stat::make('Total Apply', $totalApply)
                ->description('Applications')
                ->color('success'),
        ];
    }
}
