<?php

namespace App\Filament\Portal\Pages;

use App\Models\JobVacancy;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class Jobs extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.portal.pages.jobs';

    protected static ?string $navigationLabel = 'Jobs';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'Career Management';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'jobs';

    public function getTableQuery(): Builder
    {
        return JobVacancy::published()
            ->with(['company:id,name'])
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
                ->label('Company')
                ->searchable()
                ->sortable()
                ->placeholder('Independent'),

            Tables\Columns\TextColumn::make('location')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('employment_type')
                ->badge()
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'fulltime' => 'Full-time',
                    'parttime' => 'Part-time',
                    'intern' => 'Intern',
                    'remote' => 'Remote',
                    default => ucfirst($state),
                }),

            Tables\Columns\TextColumn::make('published_at')
                ->label('Published')
                ->dateTime('d M Y • H:i')
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('employment_type')
                ->label('Employment Type')
                ->options([
                    'fulltime' => 'Full-time',
                    'parttime' => 'Part-time',
                    'intern' => 'Intern',
                    'remote' => 'Remote',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('view')
                ->label('View Details')
                ->icon('heroicon-o-eye')
                ->url(fn (JobVacancy $record): string => url('/portal/jobs/show?job=' . $record->id)),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
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
