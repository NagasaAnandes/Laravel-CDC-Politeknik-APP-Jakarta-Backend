<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\UserRole;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'danger'  => UserRole::SUPER_ADMIN->value,
                        'warning' => UserRole::ADMIN_CDC->value,
                        'success' => UserRole::COMPANY->value,
                        'info'    => UserRole::STUDENT->value,
                        'gray'    => UserRole::ALUMNI->value,
                    ])
                    ->sortable(),

                TextColumn::make('company.name')
                    ->label('Company')
                    ->placeholder('-')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        UserRole::SUPER_ADMIN->value => 'Super Admin',
                        UserRole::ADMIN_CDC->value   => 'Admin CDC',
                        UserRole::STUDENT->value     => 'Student',
                        UserRole::ALUMNI->value      => 'Alumni',
                        UserRole::COMPANY->value     => 'Company',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])

            ->recordActions([

                EditAction::make()
                    ->visible(function ($record) {

                        /** @var \App\Models\User|null $actor */
                        $actor = Auth::user();

                        return $actor?->can('update', $record) === true;
                    }),

                DeleteAction::make()
                    ->visible(function ($record) {

                        /** @var \App\Models\User|null $actor */
                        $actor = Auth::user();

                        return $actor?->can('delete', $record) === true;
                    })
                    ->action(function ($record) {

                        /** @var \App\Models\User|null $actor */
                        $actor = Auth::user();

                        if (! $actor || ! $actor->can('delete', $record)) {
                            abort(403);
                        }

                        $record->delete();
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function ($records) {

                            /** @var \App\Models\User|null $actor */
                            $actor = Auth::user();

                            foreach ($records as $record) {

                                if (! $actor || ! $actor->can('delete', $record)) {
                                    abort(403);
                                }

                                $record->delete();
                            }
                        }),
                ]),
            ]);
    }
}
