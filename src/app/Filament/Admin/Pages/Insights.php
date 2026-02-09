<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class Insights extends Page
{
    // ❗ HARUS NON-STATIC
    // protected ?string $view = 'filament.admin.pages.insights';
    protected string $view = 'filament.admin.pages.insights';

    protected static ?string $navigationLabel = 'Insights';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 50;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public array $stats = [];

    public function mount(): void
    {
        // ===== Announcements =====
        $topAnnouncement = DB::table('announcement_views')
            ->select('announcements.id', 'announcements.title', DB::raw('COUNT(*) as views'))
            ->join('announcements', 'announcements.id', '=', 'announcement_views.announcement_id')
            ->groupBy('announcements.id', 'announcements.title')
            ->orderByDesc('views')
            ->first();

        // ===== Events =====
        $topEvent = DB::table('event_registrations')
            ->select('events.id', 'events.title', DB::raw('COUNT(*) as registrations'))
            ->join('events', 'events.id', '=', 'event_registrations.event_id')
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('registrations')
            ->first();

        // ===== Jobs =====
        $topJob = DB::table('job_application_logs')
            ->select('job_vacancies.id', 'job_vacancies.title', DB::raw('COUNT(*) as clicks'))
            ->join('job_vacancies', 'job_vacancies.id', '=', 'job_application_logs.job_vacancy_id')
            ->groupBy('job_vacancies.id', 'job_vacancies.title')
            ->orderByDesc('clicks')
            ->first();

        $this->stats = [
            // Announcements
            'announcements_total' => DB::table('announcements')->count(),
            'announcement_views'  => DB::table('announcement_views')->count(),
            'top_announcement'    => $topAnnouncement,

            // Events
            'events_total'        => DB::table('events')->count(),
            'event_views'         => DB::table('event_logs')
                ->where('action', 'view')
                ->count(),
            'event_registrations' => DB::table('event_registrations')->count(),
            'top_event'           => $topEvent,

            // Jobs
            'jobs_total'          => DB::table('job_vacancies')->count(),
            'job_apply_clicks'    => DB::table('job_application_logs')->count(),
            'top_job'             => $topJob,
        ];
    }
}
