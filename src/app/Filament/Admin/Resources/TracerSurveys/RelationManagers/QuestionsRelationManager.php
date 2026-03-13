<?php

namespace App\Filament\Admin\Resources\TracerSurveys\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('order')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('section')
                    ->badge()
                    ->sortable(),

                TextColumn::make('question_text')
                    ->label('Question')
                    ->wrap(),

                TextColumn::make('type')
                    ->badge(),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),

            ])
            ->defaultSort('order')
            ->headerActions([]);
    }
}
