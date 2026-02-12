<?php

namespace App\Filament\Admin\Pages;

use App\Models\EventRegistration;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class EventRegistrations extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin.pages.event-registrations';

    protected static ?string $navigationLabel = 'Event Registrations';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 61;

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
        return EventRegistration::query()
            ->with(['event', 'user'])
            ->when($this->period !== 'all', function ($query) {
                $query->where(
                    'registered_at',
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

                    $query->where('registered_at', '>=', now()->subDays($days));
                }),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('event.title')
                ->label('Event')
                ->searchable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('User')
                ->formatStateUsing(
                    fn($state, $record) =>
                    $record->user?->name ?? 'Guest'
                ),

            Tables\Columns\TextColumn::make('user.email')
                ->label('Email')
                ->toggleable()
                ->visible(true),

            Tables\Columns\TextColumn::make('registered_at')
                ->label('Registered At')
                ->dateTime('d M Y • H:i')
                ->sortable(),
        ];
    }

    public function export()
    {
        return response()->streamDownload(function () {
            echo "Event,User,Email,Registered At\n";

            EventRegistration::with(['event', 'user'])
                ->orderByDesc('registered_at')
                ->chunk(200, function ($registrations) {
                    foreach ($registrations as $registration) {
                        echo sprintf(
                            "\"%s\",\"%s\",\"%s\",\"%s\"\n",
                            $registration->event?->title,
                            $registration->user?->name ?? 'Guest',
                            $registration->user?->email ?? '',
                            $registration->registered_at
                        );
                    }
                });
        }, 'event-registrations.csv');
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'registered_at';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableActions(): array
    {
        return []; // No Edit / Delete
    }

    protected function getTableBulkActions(): array
    {
        return []; // No Bulk
    }
}
