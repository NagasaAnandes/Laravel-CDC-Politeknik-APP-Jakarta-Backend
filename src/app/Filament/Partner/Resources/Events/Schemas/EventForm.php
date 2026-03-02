<?php

namespace App\Filament\Partner\Resources\Events\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('event_type')
                        ->required()
                        ->options([
                            'seminar' => 'Seminar',
                            'bootcamp' => 'Bootcamp',
                            'workshop' => 'Workshop',
                        ]),
                ]),

            Section::make('Media')
                ->schema([
                    Forms\Components\FileUpload::make('poster_path')
                        ->image()
                        ->disk('public')
                        ->directory('event-posters')
                        ->nullable(),
                ]),

            Section::make('Registration Deadline')
                ->schema([
                    Forms\Components\DatePicker::make('registration_deadline')
                        ->required()
                        ->minDate(fn($context) => $context === 'create' ? today() : null)
                        ->helperText('Last date users can register'),
                ]),

            Section::make('Registration')
                ->schema([
                    Forms\Components\Select::make('registration_method')
                        ->required()
                        ->options([
                            'internal' => 'Internal',
                            'redirect' => 'Redirect',
                        ])
                        ->reactive()
                        ->afterStateUpdated(
                            fn($state, callable $set) =>
                            $state === 'redirect' ? $set('quota', null) : null
                        ),

                    Forms\Components\TextInput::make('registration_url')
                        ->url()
                        ->visible(fn($get) => $get('registration_method') === 'redirect')
                        ->required(fn($get) => $get('registration_method') === 'redirect'),

                    Forms\Components\TextInput::make('quota')
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->visible(fn($get) => $get('registration_method') === 'internal'),
                ])
                ->columns(2),
        ]);
    }
}
