<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Carbon\Carbon;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /*
            |--------------------------------------------------------------------------
            | BASIC INFORMATION
            |--------------------------------------------------------------------------
            */

            Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(150)
                        ->placeholder('e.g. Seminar Karir 2026'),

                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull()
                        ->placeholder('Describe the event details, objectives, and benefits'),

                    Forms\Components\Select::make('event_type')
                        ->required()
                        ->options([
                            'seminar'  => 'Seminar',
                            'bootcamp' => 'Bootcamp',
                            'workshop' => 'Workshop',
                        ])
                        ->placeholder('Select event type'),
                ]),

            /*
            |--------------------------------------------------------------------------
            | ORGANIZER & LOCATION
            |--------------------------------------------------------------------------
            */

            Section::make('Organizer & Location')
                ->schema([
                    Forms\Components\TextInput::make('organizer')
                        ->maxLength(100)
                        ->placeholder('e.g. CDC Politeknik APP'),

                    Forms\Components\TextInput::make('location')
                        ->maxLength(150)
                        ->placeholder('e.g. Aula Kampus / Online'),
                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | REGISTRATION DEADLINE
            |--------------------------------------------------------------------------
            */

            Section::make('Registration Deadline')
                ->schema([
                    Forms\Components\DatePicker::make('registration_deadline')
                        ->required()
                        ->label('Registration Deadline')
                        ->minDate(today())
                        ->helperText('Last date users can register for this event')
                        ->dehydrateStateUsing(function ($state) {

                            if (! $state) {
                                return null;
                            }

                            return $state instanceof Carbon
                                ? $state->copy()->endOfDay()
                                : Carbon::parse($state)->endOfDay();
                        }),
                ]),

            /*
            |--------------------------------------------------------------------------
            | REGISTRATION SETTINGS
            |--------------------------------------------------------------------------
            */

            Section::make('Registration')
                ->schema([
                    Forms\Components\Select::make('registration_method')
                        ->required()
                        ->options([
                            'internal' => 'Internal (CDC)',
                            'redirect' => 'External Redirect',
                        ])
                        ->in(['internal', 'redirect'])
                        ->reactive()
                        ->placeholder('Select registration method')
                        ->helperText('Choose "Internal" to manage registrations inside CDC, or "External" to redirect users.')
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state === 'redirect') {
                                $set('quota', null);
                            } else {
                                $set('registration_url', null);
                            }
                        }),

                    Forms\Components\TextInput::make('registration_url')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://example.com/register')
                        ->visible(fn($get) => $get('registration_method') === 'redirect')
                        ->required(fn($get) => $get('registration_method') === 'redirect'),

                    Forms\Components\TextInput::make('quota')
                        ->integer()
                        ->minValue(1)
                        ->nullable()
                        ->helperText('Leave empty for unlimited participants')
                        ->visible(fn($get) => $get('registration_method') === 'internal'),
                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | MEDIA
            |--------------------------------------------------------------------------
            */

            Section::make('Media')
                ->schema([
                    Forms\Components\FileUpload::make('poster_path')
                        ->image()
                        ->disk('public')
                        ->directory('event-posters')
                        ->visibility('public')
                        ->nullable()
                        ->imageEditor()
                        ->maxSize(2048)
                        ->imagePreviewHeight('250')
                        ->helperText('Upload event poster (optional)')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                ]),
        ]);
    }
}
