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

            /* =====================
             * BASIC INFORMATION
             * ===================== */
            Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Event Title')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('event_type')
                        ->label('Event Type')
                        ->required()
                        ->options([
                            'seminar' => 'Seminar',
                            'bootcamp' => 'Bootcamp',
                            'workshop' => 'Workshop',
                        ]),
                ]),

            /* =====================
             * ORGANIZER & LOCATION
             * ===================== */
            Section::make('Organizer & Location')
                ->schema([
                    Forms\Components\TextInput::make('organizer')
                        ->label('Organizer')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('location')
                        ->label('Location')
                        ->maxLength(150),
                ])
                ->columns(2),

            /* =====================
             * EVENT TIME
             * ===================== */
            Section::make('Event Schedule')
                ->schema([
                    Forms\Components\DateTimePicker::make('start_datetime')
                        ->label('Start Date & Time')
                        ->required(),

                    Forms\Components\DateTimePicker::make('end_datetime')
                        ->label('End Date & Time')
                        ->required()
                        ->afterOrEqual('start_datetime'),
                ])
                ->columns(2),

            /* =====================
             * REGISTRATION CONFIG
             * ===================== */
            Section::make('Registration')
                ->schema([
                    Forms\Components\Select::make('registration_method')
                        ->label('Registration Method')
                        ->required()
                        ->options([
                            'internal' => 'Internal (CDC)',
                            'redirect' => 'External Redirect',
                        ])
                        ->reactive(),

                    Forms\Components\TextInput::make('registration_url')
                        ->label('External Registration URL')
                        ->url()
                        ->maxLength(255)
                        ->visible(fn($get) => $get('registration_method') === 'redirect')
                        ->required(fn($get) => $get('registration_method') === 'redirect')
                        ->placeholder('https://example.com/register')
                        ->helperText('Required for redirect events'),

                    Forms\Components\TextInput::make('quota')
                        ->label('Quota (leave empty for unlimited)')
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->visible(fn($get) => $get('registration_method') === 'internal'),
                ])
                ->columns(2),

            /* =====================
             * MEDIA
             * ===================== */
            Section::make('Media')
                ->schema([
                    Forms\Components\FileUpload::make('poster_path')
                        ->label('Poster')
                        ->image()
                        ->disk('public')
                        ->directory('event-posters')
                        ->visibility('public')
                        ->nullable()
                        ->imageEditor(),
                ]),

            /* =====================
             * STATUS
             * ===================== */
            Section::make('Publication')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Published At')
                        ->nullable(),
                ])
                ->columns(2),
        ]);
    }
}
