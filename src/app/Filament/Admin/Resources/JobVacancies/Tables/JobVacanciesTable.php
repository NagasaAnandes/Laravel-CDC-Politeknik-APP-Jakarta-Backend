<?php

namespace App\Filament\Admin\Resources\JobVacancies\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea;

use App\Domain\Approval\ApprovalService;
use App\Enums\ApprovalStatus;
use App\Domain\Approval\Job\JobApprovalRules;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class JobVacanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employment_type')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'fulltime' => 'Full-time',
                        'parttime' => 'Part-time',
                        'intern'   => 'Intern',
                        'remote'   => 'Remote',
                        default    => ucfirst($state),
                    }),

                TextColumn::make('approval_status')
                    ->badge()
                    ->label('Approval')
                    ->colors([
                        'secondary' => ApprovalStatus::DRAFT->value,
                        'warning'   => ApprovalStatus::SUBMITTED->value,
                        'success'   => ApprovalStatus::APPROVED->value,
                        'danger'    => ApprovalStatus::REJECTED->value,
                    ])
                    ->formatStateUsing(fn(ApprovalStatus $state) => $state->label()),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->state(fn($record) => $record->isPublished())
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Immediate'),

                TextColumn::make('expired_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
            ])

            ->filters([
                SelectFilter::make('approval_status')
                    ->label('Approval Status')
                    ->options([
                        ApprovalStatus::DRAFT->value     => 'Draft',
                        ApprovalStatus::SUBMITTED->value => 'Pending',
                        ApprovalStatus::APPROVED->value  => 'Approved',
                        ApprovalStatus::REJECTED->value  => 'Rejected',
                    ]),
            ])

            ->recordActions([
                EditAction::make()
                    ->visible(fn($record) => ! $record->isApproved()),

                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn($record) => $record->approval_status === ApprovalStatus::SUBMITTED)
                    ->requiresConfirmation()
                    ->modalHeading('Approve Job')
                    ->modalDescription('Are you sure you want to approve this job?')
                    ->action(function ($record, ApprovalService $service) {


                        $user = Auth::user();

                        if (! $user || ! Gate::allows('approve', $record)) {
                            abort(403);
                        }

                        $service->approve(
                            $record,
                            $user,
                            new JobApprovalRules()
                        );

                        Notification::make()
                            ->title('Job approved successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn($record) => $record->approval_status === ApprovalStatus::SUBMITTED)
                    ->schema([
                        Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(4)
                            ->placeholder('Explain why this job is rejected...')
                    ])
                    ->modalHeading('Reject Job')
                    ->modalDescription('Provide a clear reason for rejection.')
                    ->action(function ($record, array $data, ApprovalService $service) {

                        $user = Auth::user();

                        if (! $user || ! Gate::allows('reject', $record)) {
                            abort(403);
                        }

                        $service->reject(
                            $record,
                            $user,
                            $data['reason'],
                            new JobApprovalRules()
                        );

                        Notification::make()
                            ->title('Job rejected')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn($record) => Gate::allows('delete', $record))
                    ->action(function ($record) {

                        $user = Auth::user();

                        if (! $user || ! Gate::allows('delete', $record)) {
                            abort(403);
                        }

                        $record->delete();

                        Notification::make()
                            ->title('Job deleted')
                            ->success()
                            ->send();
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function ($records) {

                            $user = Auth::user();

                            foreach ($records as $record) {

                                if (! $user || ! Gate::allows('delete', $record)) {
                                    abort(403);
                                }

                                $record->delete();
                            }

                            Notification::make()
                                ->title('Selected jobs deleted')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
