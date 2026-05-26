<?php

namespace App\Filament\Portal\Pages;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class JobApplications extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.portal.pages.job-applications';

    protected static ?string $navigationLabel = 'My Applications';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'Career Management';

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'applications';

    public function getTableQuery(): Builder
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return JobApplication::query()
            ->with(['jobVacancy.company:id,name', 'reviewedBy:id,name', 'statusLogs.changedBy:id,name'])
            ->where('user_id', $user->id)
            ->latest('applied_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('jobVacancy.title')
                ->label('Job')
                ->searchable()
                ->sortable()
                ->limit(50),

            Tables\Columns\TextColumn::make('jobVacancy.company.name')
                ->label('Company')
                ->searchable()
                ->sortable()
                ->placeholder('Independent'),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->formatStateUsing(fn (JobApplicationStatus $state) => $state->label())
                ->colors([
                    'warning' => JobApplicationStatus::PENDING->value,
                    'info' => JobApplicationStatus::REVIEWED->value,
                    'success' => JobApplicationStatus::ACCEPTED->value,
                    'danger' => JobApplicationStatus::REJECTED->value,
                ]),

            Tables\Columns\TextColumn::make('applied_at')
                ->label('Applied At')
                ->dateTime('d M Y H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('reviewed_at')
                ->label('Reviewed At')
                ->dateTime('d M Y H:i')
                ->placeholder('—')
                ->toggleable(),

            Tables\Columns\TextColumn::make('reviewedBy.name')
                ->label('Reviewed By')
                ->placeholder('—')
                ->toggleable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status')
                ->options(JobApplicationStatus::options()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('viewJob')
                ->label('View Job')
                ->icon('heroicon-o-eye')
                ->url(fn (JobApplication $record): string => url('/portal/jobs/show?job=' . $record->job_vacancy_id)),

            Action::make('timeline')
                ->label('Timeline')
                ->icon('heroicon-o-clock')
                ->modalHeading('Application Timeline')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalWidth('3xl')
                ->modalContent(fn (JobApplication $record) => view(
                    'filament.portal.pages.job-application-timeline',
                    [
                        'application' => $record->loadMissing(['jobVacancy.company', 'reviewedBy', 'statusLogs.changedBy']),
                    ]
                )),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No applications yet';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Start with a published job and your application history will appear here.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-inbox';
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            Action::make('browseJobs')
                ->label('Browse Jobs')
                ->icon('heroicon-o-briefcase')
                ->url(url('/portal/jobs')),

            Action::make('browseEvents')
                ->label('Browse Events')
                ->icon('heroicon-o-calendar-days')
                ->url(url('/portal/events')),
        ];
    }

    protected function getTableDefaultSortColumn(): ?string
    {
        return 'applied_at';
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
