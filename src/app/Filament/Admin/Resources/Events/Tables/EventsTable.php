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

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_type')
                    ->label('Type')
                    ->formatStateUsing(fn(string $state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('start_datetime')
                    ->label('Start')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('end_datetime')
                    ->label('End')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('registration_method')
                    ->label('Registration')
                    ->formatStateUsing(
                        fn(string $state) =>
                        $state === 'internal' ? 'Internal' : 'Redirect'
                    )
                    ->sortable(),

                TextColumn::make('quota')
                    ->label('Quota')
                    ->formatStateUsing(fn($state) => $state ?? 'Unlimited')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->options([
                        'seminar' => 'Seminar',
                        'bootcamp' => 'Bootcamp',
                        'workshop' => 'Workshop',
                    ]),

                SelectFilter::make('registration_method')
                    ->label('Registration')
                    ->options([
                        'internal' => 'Internal',
                        'redirect' => 'Redirect',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make()
                    ->visible(fn($record) => $record->registrations()->count() === 0),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(
                            fn($records) =>
                            $records->every(fn($record) => $record->registrations()->count() === 0)
                        ),
                ]),
            ])
            ->defaultSort('start_datetime', 'desc');
    }
}
