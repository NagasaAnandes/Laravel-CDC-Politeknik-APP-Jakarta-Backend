<?php

namespace App\Filament\Portal\Pages;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class Events extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.portal.pages.events';

    protected static ?string $navigationLabel = 'Events';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Career Management';

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'events';

    public function getTableQuery(): Builder
    {
        return Event::published()
            ->with(['company:id,name'])
            ->withCount('registrations')
            ->latest('published_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->searchable()
                ->sortable()
                ->limit(50),

            Tables\Columns\TextColumn::make('company.name')
                ->label('Organizer')
                ->searchable()
                ->sortable()
                ->placeholder('Internal / External'),

            Tables\Columns\TextColumn::make('event_type')
                ->badge()
                ->formatStateUsing(fn (string $state) => ucfirst($state)),

            Tables\Columns\TextColumn::make('location')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('registration_deadline')
                ->label('Deadline')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('registrations_count')
                ->label('Registrations')
                ->badge()
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('event_type')
                ->label('Event Type')
                ->options([
                    'seminar' => 'Seminar',
                    'workshop' => 'Workshop',
                    'campus' => 'Campus Event',
                    'career' => 'Career Event',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('view')
                ->label('View Details')
                ->icon('heroicon-o-eye')
                ->url(fn (Event $record): string => url('/portal/events/show?event=' . $record->id)),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No events found';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Try a broader filter or come back later when new campus and career events are published.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-calendar-days';
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            Action::make('browseJobs')
                ->label('Browse Jobs')
                ->icon('heroicon-o-briefcase')
                ->url(url('/portal/jobs')),

            Action::make('resetFilters')
                ->label('Reset Filters')
                ->icon('heroicon-o-arrow-path')
                ->url(url('/portal/events')),
        ];
    }

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'published_at';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'desc';
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }
}
