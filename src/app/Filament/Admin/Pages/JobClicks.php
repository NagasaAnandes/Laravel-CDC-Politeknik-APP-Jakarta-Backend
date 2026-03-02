<?php

namespace App\Filament\Admin\Pages;

use App\Models\JobApplicationLog;
use Carbon\Carbon;
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

    protected static ?string $navigationLabel = 'Job Clicks';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 62;

    public string $period = 'all';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn() => $this->export()),
        ];
    }

    public function getTableQuery(): Builder
    {
        return JobApplicationLog::query()
            ->with(['jobVacancy', 'user'])
            ->when($this->period !== 'all', function ($query) {
                $query->where(
                    'clicked_at',
                    '>=',
                    Carbon::now()->subDays(
                        match ($this->period) {
                            '7d' => 7,
                            '30d' => 30,
                            default => 0,
                        }
                    )
                );
            });
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('period')
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

                    $query->where('clicked_at', '>=', now()->subDays($days));
                }),
        ];
    }


    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('jobVacancy.title')
                ->label('Job')
                ->searchable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('User')
                ->formatStateUsing(
                    fn($state, $record) =>
                    $record->user?->name ?? 'Guest'
                ),

            Tables\Columns\TextColumn::make('clicked_at')
                ->label('Clicked At')
                ->dateTime('d M Y • H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('user.email')
                ->label('Email')
                ->toggleable()
                ->visible(true),

            Tables\Columns\TextColumn::make('ip_address')
                ->label('IP Address'),
        ];
    }

    public function export()
    {
        return response()->streamDownload(function () {
            echo "Job,User,Email,Clicked At,IP\n";

            JobApplicationLog::with(['jobVacancy', 'user'])
                ->orderByDesc('clicked_at')
                ->chunk(200, function ($logs) {
                    foreach ($logs as $log) {
                        echo sprintf(
                            "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                            $log->jobVacancy?->title,
                            $log->user?->name ?? 'Guest',
                            $log->user?->email ?? '',
                            $log->clicked_at,
                            $log->ip_address
                        );
                    }
                });
        }, 'job-clicks.csv');
    }


    protected function isTablePaginationEnabled(): bool
    {
        return true; // Bisa kamu ubah false kalau mau tanpa pagination
    }

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'clicked_at';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableActions(): array
    {
        return []; // ❌ No Edit / Delete
    }

    protected function getTableBulkActions(): array
    {
        return []; // ❌ No Bulk
    }
}
