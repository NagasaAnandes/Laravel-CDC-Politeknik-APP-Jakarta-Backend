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
        $now = now();
        $last30 = $now->copy()->subDays(30);

        $totalJobs = JobVacancy::count();
        $publishedJobs = JobVacancy::published()->count();

        $totalClicks = JobApplicationLog::count();
        $clicksLast30 = JobApplicationLog::where('clicked_at', '>=', $last30)->count();

        $guestClicks = JobApplicationLog::whereNull('user_id')->count();

        return [
            Stat::make('Total Jobs', $totalJobs)
                ->description('All time')
                ->color('primary'),

            Stat::make('Published Jobs', $publishedJobs)
                ->description('Currently active')
                ->color('success'),

            Stat::make('Total Clicks', $totalClicks)
                ->description('All time')
                ->color('info'),
        ];
    }
}
