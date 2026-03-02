<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

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

            Section::make('Organizer & Location')
                ->schema([
                    Forms\Components\TextInput::make('organizer')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('location')
                        ->maxLength(150),
                ])
                ->columns(2),

            Section::make('Registration Deadline')
                ->schema([
                    Forms\Components\DatePicker::make('registration_deadline')
                        ->required()
                        ->label('Registration Deadline')
                        ->minDate(fn($context) => $context === 'create' ? today() : null)
                        ->helperText('Last date users can register for this event')
                        ->dehydrateStateUsing(function ($state) {

                            if (! $state) {
                                return null;
                            }

                            return $state instanceof \Carbon\Carbon
                                ? $state->copy()->endOfDay()
                                : \Carbon\Carbon::parse($state)->endOfDay();
                        }),
                ]),

            Section::make('Registration')
                ->schema([
                    Forms\Components\Select::make('registration_method')
                        ->required()
                        ->options([
                            'internal' => 'Internal (CDC)',
                            'redirect' => 'External Redirect',
                        ])
                        ->reactive()
                        ->afterStateUpdated(
                            fn($state, callable $set) =>
                            $state === 'redirect' ? $set('quota', null) : null
                        ),

                    Forms\Components\TextInput::make('registration_url')
                        ->url()
                        ->maxLength(255)
                        ->visible(fn($get) => $get('registration_method') === 'redirect')
                        ->required(fn($get) => $get('registration_method') === 'redirect'),

                    Forms\Components\TextInput::make('quota')
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->visible(fn($get) => $get('registration_method') === 'internal'),
                ])
                ->columns(2),

            Section::make('Media')
                ->schema([
                    Forms\Components\FileUpload::make('poster_path')
                        ->image()
                        ->disk('public')
                        ->directory('event-posters')
                        ->visibility('public')
                        ->nullable()
                        ->imageEditor(),
                ]),
        ]);
    }
}
