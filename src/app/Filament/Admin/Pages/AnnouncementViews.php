<?php

namespace App\Filament\Admin\Pages;

use App\Models\AnnouncementView;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementViews extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin.pages.announcement-views';

    protected static ?string $navigationLabel = 'Announcement Views';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 63;

    public string $period = 'all';

    /* ==========================
     * HEADER ACTIONS
     * ========================== */

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn() => $this->export()),
        ];
    }

    /* ==========================
     * TABLE QUERY
     * ========================== */

    public function getTableQuery(): Builder
    {
        return AnnouncementView::query()
            ->with(['announcement', 'user'])
            ->when($this->period !== 'all', function ($query) {
                $query->where(
                    'viewed_at',
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

    /* ==========================
     * FILTERS
     * ========================== */

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('period')
                ->options([
                    'all' => 'All Time',
                    '7d'  => 'Last 7 Days',
                    '30d' => 'Last 30 Days',
                ])
                ->query(function (Builder $query, array $data) {

                    if (! $data['value'] || $data['value'] === 'all') {
                        return;
                    }

                    $days = match ($data['value']) {
                        '7d'  => 7,
                        '30d' => 30,
                    };

                    $query->where('viewed_at', '>=', now()->subDays($days));
                }),
        ];
    }

    /* ==========================
     * COLUMNS
     * ========================== */

    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('announcement.title')
                ->label('Announcement')
                ->searchable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Viewer')
                ->formatStateUsing(
                    fn($state, $record) =>
                    $record->user?->name ?? 'Guest'
                )
                ->searchable(),

            Tables\Columns\TextColumn::make('user.email')
                ->label('Email')
                ->visible(true)
                ->formatStateUsing(
                    fn($state, $record) =>
                    $record->user?->email ?? '-'
                )
                ->toggleable(),

            Tables\Columns\TextColumn::make('viewed_at')
                ->label('Viewed At')
                ->dateTime('d M Y • H:i')
                ->sortable(),
        ];
    }

    /* ==========================
     * EXPORT
     * ========================== */

    public function export()
    {
        return response()->streamDownload(function () {

            echo "Announcement,Viewer,Email,Viewed At\n";

            $this->getFilteredTableQuery()
                ->orderByDesc('viewed_at')
                ->chunk(200, function ($logs) {

                    foreach ($logs as $log) {

                        echo sprintf(
                            "\"%s\",\"%s\",\"%s\",\"%s\"\n",
                            $log->announcement?->title,
                            $log->user?->name ?? 'Guest',
                            $log->user?->email ?? '',
                            $log->viewed_at
                        );
                    }
                });
        }, 'announcement-views.csv');
    }

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'viewed_at';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableActions(): array
    {
        return [];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
