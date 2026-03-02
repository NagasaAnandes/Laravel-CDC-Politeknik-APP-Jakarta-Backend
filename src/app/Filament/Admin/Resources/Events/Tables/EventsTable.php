<?php

namespace App\Filament\Admin\Resources\Events\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use App\Domain\Approval\ApprovalService;
use App\Domain\Approval\Event\EventApprovalRules;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApprovalStatus;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->withCount('registrations')
            )
            ->columns([

                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(ApprovalStatus $state) => $state->label())
                    ->colors([
                        'secondary' => ApprovalStatus::DRAFT->value,
                        'warning'   => ApprovalStatus::PENDING->value,
                        'success'   => ApprovalStatus::APPROVED->value,
                        'danger'    => ApprovalStatus::REJECTED->value,
                    ])
                    ->sortable(),

                TextColumn::make('event_type')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('registration_deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('quota')
                    ->label('Quota')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {

                        if ($record->registration_method !== 'internal') {
                            return 'External';
                        }

                        if ($state === null) {
                            return 'Unlimited';
                        }

                        if ($record->registrations_count >= $state) {
                            return 'Full';
                        }

                        return "{$record->registrations_count} / {$state}";
                    })
                    ->colors([
                        'secondary' => fn($record) => $record->registration_method !== 'internal',
                        'success'   => fn($record) =>
                        $record->quota !== null &&
                            $record->registrations_count < $record->quota,
                        'danger'    => fn($record) =>
                        $record->quota !== null &&
                            $record->registrations_count >= $record->quota,
                    ]),

                IconColumn::make('registration_open')
                    ->label('Open')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->isRegistrationOpen())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('approval_status')
                    ->options([
                        ApprovalStatus::DRAFT->value    => 'Draft',
                        ApprovalStatus::PENDING->value  => 'Pending',
                        ApprovalStatus::APPROVED->value => 'Approved',
                        ApprovalStatus::REJECTED->value => 'Rejected',
                    ]),
            ])
            ->recordActions([

                EditAction::make(),

                Action::make('approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(
                        fn($record) =>
                        $record->approval_status === ApprovalStatus::PENDING
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(ApprovalService::class)->approve(
                            model: $record,
                            actor: Auth::user(),
                            rules: app(EventApprovalRules::class)
                        );
                    }),

                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(
                        fn($record) =>
                        $record->approval_status === ApprovalStatus::PENDING
                    )
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(function ($record, array $data) {
                        app(ApprovalService::class)->reject(
                            model: $record,
                            actor: Auth::user(),
                            reason: $data['reason'],
                            rules: app(EventApprovalRules::class)
                        );
                    }),

                Action::make('revert')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(
                        fn($record) =>
                        $record->approval_status === ApprovalStatus::APPROVED
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(ApprovalService::class)->revert(
                            model: $record,
                            actor: Auth::user(),
                            rules: app(EventApprovalRules::class)
                        );
                    }),

                DeleteAction::make()
                    ->visible(
                        fn($record) =>
                        $record->approval_status !== ApprovalStatus::APPROVED
                            && $record->registrations_count === 0
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(
                            fn($records) =>
                            $records->every(
                                fn($record) =>
                                $record->approval_status !== ApprovalStatus::APPROVED
                                    && $record->registrations_count === 0
                            )
                        ),
                ]),
            ])
            ->defaultSort('registration_deadline', 'desc');
    }
}
