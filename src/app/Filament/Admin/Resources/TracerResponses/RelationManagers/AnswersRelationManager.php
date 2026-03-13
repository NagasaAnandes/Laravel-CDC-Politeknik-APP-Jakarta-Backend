<?php

namespace App\Filament\Admin\Resources\TracerResponses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $title = 'Answers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('question.question_text')
                    ->label('Question')
                    ->wrap(),

                TextColumn::make('answer_value')
                    ->label('Answer')
                    ->wrap(),

                TextColumn::make('answer_json')
                    ->label('Multiple Choice')
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return null;
                        }

                        return implode(', ', $state);
                    }),

            ])

            ->headerActions([]);
    }
}
