<?php

namespace App\Filament\Admin\Widgets;

use App\Models\JobApplicationLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class JobClickStats extends BaseWidget
{
    protected function getStats(): array
    {
        $now = now();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        // Ambil total dan guest sekaligus (1 query)
        $totals = JobApplicationLog::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) as guest
            ')
            ->first();

        $last30 = JobApplicationLog::where('clicked_at', '>=', $thirtyDaysAgo)->count();

        return [
            Stat::make('Total Clicks', $totals->total ?? 0)
                ->description('All time')
                ->color('primary'),

            Stat::make('Last 30 Days', $last30)
                ->description('Recent activity')
                ->color('success'),

            Stat::make('Guest Clicks', $totals->guest ?? 0)
                ->description('Unauthenticated users')
                ->color('warning'),
        ];
    }
}
