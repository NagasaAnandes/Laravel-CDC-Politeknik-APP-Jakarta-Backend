<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /* =====================
             * BASIC INFORMATION
             * ===================== */
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

            /* =====================
             * ORGANIZER & LOCATION
             * ===================== */
            Forms\Components\TextInput::make('organizer')
                ->label('Organizer')
                ->maxLength(100),

            Forms\Components\TextInput::make('location')
                ->label('Location')
                ->maxLength(150),

            /* =====================
             * EVENT TIME
             * ===================== */
            Forms\Components\DateTimePicker::make('start_datetime')
                ->label('Start Date & Time')
                ->required(),

            Forms\Components\DateTimePicker::make('end_datetime')
                ->label('End Date & Time')
                ->required()
                ->after('start_datetime'),

            /* =====================
             * REGISTRATION CONFIG
             * ===================== */
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
                ->helperText('Wajib diisi jika menggunakan redirect'),

            /* =====================
             * STATUS & QUOTA
             * ===================== */
            Forms\Components\TextInput::make('quota')
                ->label('Quota (kosongkan jika unlimited)')
                ->numeric()
                ->minValue(0)
                ->nullable(),

            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true),

            Forms\Components\DateTimePicker::make('published_at')
                ->label('Published At')
                ->nullable(),
        ]);
    }
}
