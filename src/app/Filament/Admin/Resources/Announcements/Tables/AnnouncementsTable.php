<?php

namespace App\Filament\Admin\Resources\Announcements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;


class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('category')
                    ->badge(),

                TextColumn::make('priority')
                    ->badge(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('expired_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'career'   => 'Career',
                        'academic' => 'Academic',
                        'event'    => 'Event',
                        'general'  => 'General',
                    ]),

                SelectFilter::make('priority')
                    ->options([
                        'normal'    => 'Normal',
                        'important' => 'Important',
                        'urgent'    => 'Urgent',
                    ]),

                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
