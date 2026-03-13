<?php

namespace App\Filament\Admin\Resources\TracerSurveys\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TracerSurveyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Survey Information')
                ->schema([

                    TextInput::make('title')
                        ->label('Survey Title')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('year')
                        ->label('Year')
                        ->numeric()
                        ->required(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Active Survey')
                        ->default(false),

                ])
                ->columns(2)

        ]);
    }
}
