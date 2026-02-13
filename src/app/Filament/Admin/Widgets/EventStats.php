<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventStats extends BaseWidget
{
    protected static ?int $sort = 21;

    protected function getStats(): array
    {
        $now = now();
        $last30 = $now->copy()->subDays(30);

        $totalEvents = Event::count();
        $publishedEvents = Event::published()->count();

        $totalRegistrations = EventRegistration::count();
        $registrationsLast30 = EventRegistration::where('registered_at', '>=', $last30)->count();

        $totalViews = EventLog::where('action', 'view')->count();

        return [
            Stat::make('Total Events', $totalEvents)
                ->description('All time')
                ->color('primary'),

            Stat::make('Published Events', $publishedEvents)
                ->description('Currently active')
                ->color('success'),

            Stat::make('Total Registrations', $totalRegistrations)
                ->description('All time')
                ->color('info'),

            // Stat::make('Registrations (30 Days)', $registrationsLast30)
            //     ->description('Recent activity')
            //     ->color('warning'),

            // Stat::make('Event Views', $totalViews)
            //     ->description('Detail page views')
            //     ->color('gray'),
        ];
    }
}
