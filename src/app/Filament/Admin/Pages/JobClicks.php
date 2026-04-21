<?php

namespace App\Filament\Admin\Pages;

use App\Models\JobApplicationLog;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class JobClicks extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin.pages.job-clicks';

    protected static ?string $navigationLabel = 'Job Tracking';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 62;

    public string $period = 'all';

    /*
    |--------------------------------------------------------------------------
    | Header Actions
    |--------------------------------------------------------------------------
    */

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn() => $this->export()),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Query (SOURCE OF TRUTH)
    |--------------------------------------------------------------------------
    */

    public function getTableQuery(): Builder
    {
        return JobApplicationLog::query()
            ->with(['jobVacancy', 'user'])
            ->when($this->period !== 'all', function ($query) {
                $query->where(
                    'created_at',
                    '>=',
                    now()->subDays(
                        match ($this->period) {
                            '7d' => 7,
                            '30d' => 30,
                            default => 0,
                        }
                    )
                );
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('event_type')
                ->label('Event Type')
                ->options([
                    'click' => 'Click',
                    'apply' => 'Apply',
                ]),

            Tables\Filters\SelectFilter::make('job_vacancy_id')
                ->label('Job')
                ->relationship('jobVacancy', 'title')
                ->searchable(),

            Tables\Filters\SelectFilter::make('period')
                ->label('Period')
                ->options([
                    'all' => 'All Time',
                    '7d' => 'Last 7 Days',
                    '30d' => 'Last 30 Days',
                ])
                ->query(function (Builder $query, array $data) {
                    if (! $data['value'] || $data['value'] === 'all') {
                        return;
                    }

                    $days = match ($data['value']) {
                        '7d' => 7,
                        '30d' => 30,
                    };

                    $query->where('created_at', '>=', now()->subDays($days));
                }),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Columns
    |--------------------------------------------------------------------------
    */

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('jobVacancy.title')
                ->label('Job')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('event_type')
                ->label('Event')
                ->badge()
                ->color(fn($state) => $state === 'apply' ? 'success' : 'info'),

            Tables\Columns\TextColumn::make('user.name')
                ->label('User')
                ->formatStateUsing(
                    fn($state, $record) =>
                    $record->user?->name ?? 'Guest'
                ),

            Tables\Columns\TextColumn::make('user.email')
                ->label('Email')
                ->toggleable(),

            Tables\Columns\TextColumn::make('session_id')
                ->label('Session')
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('ip_address')
                ->label('IP Address')
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('user_agent')
                ->label('User Agent')
                ->limit(40)
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Time')
                ->dateTime('d M Y • H:i')
                ->sortable(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Export (SYNCED WITH FILTERS)
    |--------------------------------------------------------------------------
    */

    public function export()
    {
        return response()->streamDownload(function () {

            echo "Job,Event,User,Email,Time,Session,IP\n";

            $query = $this->getTableQuery();

            $query->orderByDesc('created_at')
                ->chunk(200, function ($logs) {

                    foreach ($logs as $log) {
                        echo sprintf(
                            "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                            $log->jobVacancy?->title,
                            $log->event_type,
                            $log->user?->name ?? 'Guest',
                            $log->user?->email ?? '',
                            $log->created_at,
                            $log->session_id,
                            $log->ip_address
                        );
                    }
                });
        }, 'job-tracking.csv');
    }

    /*
    |--------------------------------------------------------------------------
    | Table Config
    |--------------------------------------------------------------------------
    */

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'created_at';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableActions(): array
    {
        return []; // log = immutable
    }

    protected function getTableBulkActions(): array
    {
        return []; // no bulk
    }
}
