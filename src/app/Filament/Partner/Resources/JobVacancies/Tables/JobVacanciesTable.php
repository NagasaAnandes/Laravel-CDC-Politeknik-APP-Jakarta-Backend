<?php

namespace App\Filament\Partner\Resources\JobVacancies\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use App\Enums\ApprovalStatus;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Domain\Approval\ApprovalService;
use App\Domain\Approval\Job\JobApprovalRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class JobVacanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
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
                    ->formatStateUsing(fn(ApprovalStatus $state) => $state->label())
                    ->colors([
                        'secondary' => fn($state) => $state === ApprovalStatus::DRAFT,
                        'warning'   => fn($state) => $state === ApprovalStatus::PENDING,
                        'success'   => fn($state) => $state === ApprovalStatus::APPROVED,
                        'danger'    => fn($state) => $state === ApprovalStatus::REJECTED,
                    ]),

                IconColumn::make('status')
                    ->label('Published')
                    ->state(fn($record) => $record->isPublished())
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->placeholder('—'),

                TextColumn::make('expired_at')
                    ->date()
                    ->placeholder('—'),
            ])

            ->recordActions([
                EditAction::make()
                    ->visible(
                        fn($record) =>
                        in_array($record->approval_status, [
                            ApprovalStatus::DRAFT,
                            ApprovalStatus::REJECTED,
                        ], true)
                    ),

                Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(
                        fn($record) =>
                        in_array($record->approval_status, [
                            ApprovalStatus::DRAFT,
                            ApprovalStatus::REJECTED,
                        ], true)
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        Gate::authorize('submit', $record);

                        app(ApprovalService::class)
                            ->submit(
                                model: $record,
                                actor: Auth::user(),
                                rules: new JobApprovalRules()
                            );

                        Notification::make()
                            ->title('Job submitted for approval')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(
                        fn($record) =>
                        in_array($record->approval_status, [
                            ApprovalStatus::DRAFT,
                            ApprovalStatus::REJECTED,
                        ], true)
                    )
                    ->action(function ($record) {

                        Gate::authorize('delete', $record);

                        if (! in_array($record->approval_status, [
                            ApprovalStatus::DRAFT,
                            ApprovalStatus::REJECTED,
                        ], true)) {
                            abort(403);
                        }

                        $record->delete();
                    })

            ]);
    }
}
