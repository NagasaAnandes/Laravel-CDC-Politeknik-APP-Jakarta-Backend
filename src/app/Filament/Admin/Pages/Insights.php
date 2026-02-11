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

    public ?\Illuminate\Support\Carbon $periodStart = null;

    protected static ?string $navigationLabel = 'Insights';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 50;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public string $period = 'all';

    public array $stats = [];

    public function mount(): void
    {
        $requestedPeriod = request()->query('period', 'all');

        // Allowed periods
        if (! in_array($requestedPeriod, ['all', '7d', '30d'], true)) {
            $requestedPeriod = 'all';
        }

        $this->period = $requestedPeriod;

        $this->periodStart = $this->resolvePeriodStart();

        // ===== Announcements =====
        $topAnnouncement = DB::table('announcement_views')
            ->select('announcements.id', 'announcements.title', DB::raw('COUNT(*) as views'))
            ->join('announcements', 'announcements.id', '=', 'announcement_views.announcement_id')
            ->when($this->periodStart, function ($q) {
                $q->where('announcement_views.viewed_at', '>=', $this->periodStart);
            })
            ->groupBy('announcements.id', 'announcements.title')
            ->orderByDesc('views')
            ->first();


        // ===== Events =====
        $topEvent = DB::table('event_registrations')
            ->select('events.id', 'events.title', DB::raw('COUNT(*) as registrations'))
            ->join('events', 'events.id', '=', 'event_registrations.event_id')
            ->when($this->periodStart, function ($q) {
                $q->where('event_registrations.registered_at', '>=', $this->periodStart);
            })
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('registrations')
            ->first();


        // ===== Jobs =====
        $topJob = DB::table('job_application_logs')
            ->select('job_vacancies.id', 'job_vacancies.title', DB::raw('COUNT(*) as clicks'))
            ->join('job_vacancies', 'job_vacancies.id', '=', 'job_application_logs.job_vacancy_id')
            ->when($this->periodStart, function ($q) {
                $q->where('job_application_logs.clicked_at', '>=', $this->periodStart);
            })
            ->groupBy('job_vacancies.id', 'job_vacancies.title')
            ->orderByDesc('clicks')
            ->first();


        $this->stats = [
            // Announcements
            'announcements_total' => DB::table('announcements')->count(),
            'announcement_views' => DB::table('announcement_views')
                ->when($this->periodStart, function ($q) {
                    $q->where('viewed_at', '>=', $this->periodStart);
                })
                ->count(),

            'top_announcement'    => $topAnnouncement,

            // Events
            'events_total'        => DB::table('events')->count(),
            'event_views'         => DB::table('event_logs')
                ->where('action', 'view')
                ->count(),
            'event_registrations' => DB::table('event_registrations')
                ->when($this->periodStart, function ($q) {
                    $q->where('registered_at', '>=', $this->periodStart);
                })
                ->count(),
            'top_event'           => $topEvent,

            // Jobs
            'jobs_total'          => DB::table('job_vacancies')->count(),
            'job_apply_clicks' => DB::table('job_application_logs')
                ->when($this->periodStart, function ($q) {
                    $q->where('clicked_at', '>=', $this->periodStart);
                })
                ->count(),

            'top_job'             => $topJob,
        ];
    }

    protected function resolvePeriodStart(): ?\Illuminate\Support\Carbon
    {
        return match ($this->period) {
            '7d'  => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => null, // all time
        };
    }
}
