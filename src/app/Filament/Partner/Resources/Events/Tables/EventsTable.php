<?php

namespace App\Filament\Partner\Resources\Events\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use App\Domain\Approval\ApprovalService;
use App\Domain\Approval\Event\EventApprovalRules;
use App\Enums\ApprovalStatus;
use Illuminate\Support\Facades\Auth;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->where('company_id', Auth::user()->company_id)
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable(),

                TextColumn::make('approval_status')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state->value)),

                TextColumn::make('registration_deadline')
                    ->label('Deadline')
                    ->date('d M Y'),

                IconColumn::make('registration_deadline')
                    ->label('Open')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->isRegistrationOpen())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->recordActions([

                EditAction::make()
                    ->visible(
                        fn($record) =>
                        $record->approval_status === ApprovalStatus::DRAFT
                            || $record->approval_status === ApprovalStatus::REJECTED
                    ),

                Action::make('submit')
                    ->color('warning')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(
                        fn($record) =>
                        $record->approval_status === ApprovalStatus::DRAFT
                            || $record->approval_status === ApprovalStatus::REJECTED
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(ApprovalService::class)->submit(
                            model: $record,
                            actor: Auth::user(),
                            rules: app(EventApprovalRules::class)
                        );
                    }),
            ]);
    }
}
