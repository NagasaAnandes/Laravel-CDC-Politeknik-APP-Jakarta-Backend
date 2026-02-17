<?php

namespace App\Filament\Admin\Resources\JobVacancies\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea;

use App\Domain\Approval\ApprovalService;
use App\Enums\ApprovalStatus;

use Illuminate\Support\Facades\Auth;


class JobVacanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_name')
                    ->sortable(),

                TextColumn::make('employment_type')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'fulltime' => 'Full-time',
                        'parttime' => 'Part-time',
                        'intern' => 'Intern',
                        'remote' => 'Remote',
                        default => ucfirst($state),
                    }),

                TextColumn::make('approval_status')
                    ->badge()
                    ->label('Approval')
                    ->colors([
                        'secondary' => ApprovalStatus::DRAFT->value,
                        'warning'   => ApprovalStatus::PENDING->value,
                        'success'   => ApprovalStatus::APPROVED->value,
                        'danger'    => ApprovalStatus::REJECTED->value,
                    ])
                    ->formatStateUsing(fn(ApprovalStatus $state) => $state->label()),


                IconColumn::make('status')
                    ->label('Published')
                    ->state(fn($record) => $record->isPublished())
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime()
                    ->placeholder('Immediate'),

                TextColumn::make('expired_at')
                    ->label('Expires At')
                    ->date()
                    ->placeholder('—'),
            ])

            ->filters([
                // optional, jika mau
            ])

            ->recordActions([
                EditAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(
                        fn($record) =>
                        $record->approval_status === ApprovalStatus::PENDING
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        if (! $user || ! $user->can('approve', $record)) {
                            abort(403);
                        }

                        app(ApprovalService::class)
                            ->approve($record, $user);

                        Notification::make()
                            ->title('Job approved successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(
                        fn($record) =>
                        $record->approval_status === ApprovalStatus::PENDING
                    )
                    ->form([
                        Textarea::make('reason')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        if (! $user || ! $user->can('reject', $record)) {
                            abort(403);
                        }

                        app(ApprovalService::class)
                            ->reject($record, $user, $data['reason']);

                        Notification::make()
                            ->title('Job rejected')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
