<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Announcement;
use App\Models\AnnouncementView;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnnouncementStats extends BaseWidget
{
    protected static ?int $sort = 20;

    protected function getStats(): array
    {
        $now = now();
        $last30 = $now->copy()->subDays(30);

        $totalAnnouncements = Announcement::count();
        $activeAnnouncements = Announcement::published()->count();

        $views = AnnouncementView::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) as guest
        ')->first();

        $viewsLast30 = AnnouncementView::where('viewed_at', '>=', $last30)->count();

        return [
            Stat::make('Total Announcements', $totalAnnouncements)
                ->description('All time')
                ->color('primary'),

            Stat::make('Active Announcements', $activeAnnouncements)
                ->description('Currently published')
                ->color('success'),

            Stat::make('Total Views', $views->total ?? 0)
                ->description('All time')
                ->color('info'),

            // Stat::make('Views (30 Days)', $viewsLast30)
            //     ->description('Recent activity')
            //     ->color('warning'),

            // Stat::make('Guest Views', $views->guest ?? 0)
            //     ->description('Unauthenticated')
            //     ->color('gray'),
        ];
    }
}
