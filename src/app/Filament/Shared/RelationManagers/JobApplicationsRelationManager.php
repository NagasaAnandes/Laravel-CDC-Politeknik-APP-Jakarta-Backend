<?php

namespace App\Filament\Shared\RelationManagers;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class JobApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobApplications';

    protected static ?string $title = 'Applicants';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (JobApplicationStatus $state) => $state->label())
                    ->colors([
                        'warning' => JobApplicationStatus::PENDING->value,
                        'info' => JobApplicationStatus::REVIEWED->value,
                        'success' => JobApplicationStatus::ACCEPTED->value,
                        'danger' => JobApplicationStatus::REJECTED->value,
                    ]),

                TextColumn::make('applied_at')
                    ->label('Applied At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('reviewed_at')
                    ->label('Reviewed At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Not reviewed yet')
                    ->toggleable(),

                TextColumn::make('reviewedBy.name')
                    ->label('Reviewed By')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('internal_note')
                    ->label('Internal Note')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable()
                    ->visible(fn (JobApplication $record): bool => \Illuminate\Support\Facades\Gate::allows('viewInternalNote', $record)),

                TextColumn::make('note')
                    ->label('Applicant Note')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(JobApplicationStatus::options()),
            ])
            ->recordActions([
                Action::make('viewCv')
                    ->label('View CV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (JobApplication $record): ?string => filled($record->user?->cv_path)
                        ? route('cv.download', ['application' => $record->id])
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (JobApplication $record): bool => filled($record->user?->cv_path) && \Illuminate\Support\Facades\Gate::allows('view', $record)),

                Action::make('timeline')
                    ->label('Timeline')
                    ->icon('heroicon-o-clock')
                    ->modalHeading('Application Timeline')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('3xl')
                    ->modalContent(fn (JobApplication $record) => view(
                        'filament.shared.job-application-timeline',
                        [
                            'application' => $record->loadMissing(['jobVacancy.company', 'statusLogs.changedBy', 'reviewedBy']),
                        ]
                    )),

                Action::make('review')
                    ->label('Update Status')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options(JobApplicationStatus::options())
                            ->helperText('Only valid workflow transitions are accepted.')
                            ->required(),

                        Textarea::make('note')
                            ->label('Internal Note')
                            ->rows(4)
                            ->maxLength(2000),
                    ])
                    ->modalWidth('lg')
                    ->visible(fn (JobApplication $record): bool => ! $record->status->isFinal())
                    ->action(function (JobApplication $record, array $data): void {
                        Gate::authorize('update', $record);

                        $fromStatus = $record->status;
                        $nextStatus = JobApplicationStatus::from($data['status']);

                        if (! $fromStatus->canTransitionTo($nextStatus)) {
                            abort(422, 'Invalid status transition.');
                        }

                        $reviewerId = auth()->id();

                        $record->update([
                            'status' => $nextStatus,
                            'internal_note' => $data['note'] ?? $record->internal_note,
                            'reviewed_by' => $reviewerId,
                            'reviewed_at' => now(),
                        ]);

                        $record->statusLogs()->create([
                            'from_status' => $fromStatus,
                            'to_status' => $nextStatus,
                            'changed_by' => $reviewerId,
                            'note' => $data['note'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Application updated')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
