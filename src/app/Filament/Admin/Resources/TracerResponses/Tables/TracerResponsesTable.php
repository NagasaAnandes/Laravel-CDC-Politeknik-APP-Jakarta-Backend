<?php

namespace App\Filament\Admin\Resources\TracerResponses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class TracerResponsesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('user.name')
                    ->label('Alumni')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email'),

                TextColumn::make('survey.title')
                    ->label('Survey')
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),

            ])

            ->defaultSort('submitted_at', 'desc')

            ->filters([
                //
            ])

            ->recordActions([
                ViewAction::make(),
            ])

            ->toolbarActions([]);
    }
}
