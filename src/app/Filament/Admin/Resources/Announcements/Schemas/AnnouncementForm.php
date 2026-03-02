<?php

namespace App\Filament\Admin\Resources\Announcements\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /* =========================
             * BASIC INFORMATION
             * ========================= */
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('category')
                ->required()
                ->options([
                    'career'   => 'Career',
                    'academic' => 'Academic',
                    'event'    => 'Event',
                    'general'  => 'General',
                ]),

            Forms\Components\Select::make('priority')
                ->required()
                ->options([
                    'normal'    => 'Normal',
                    'important' => 'Important',
                    'urgent'    => 'Urgent',
                ]),

            Forms\Components\Select::make('target_audience')
                ->required()
                ->options([
                    'student' => 'Student',
                    'alumni'  => 'Alumni',
                    'all'     => 'All',
                ]),

            Forms\Components\Textarea::make('content')
                ->required()
                ->rows(8)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('redirect_url')
                ->url()
                ->nullable()
                ->placeholder('https://example.com')
                ->helperText('Optional: gunakan jika announcement hanya berupa redirect'),

            /* =========================
             * PUBLISH CONFIG
             * ========================= */

            Forms\Components\Toggle::make('is_active')
                ->label('Publish')
                ->helperText('Saat diaktifkan pertama kali, sistem akan otomatis mengisi tanggal publikasi.')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if ($state && ! $get('published_at')) {
                        $set('published_at', now());
                    }
                }),

            Forms\Components\Hidden::make('published_at'),

            Forms\Components\DateTimePicker::make('expired_at')
                ->nullable()
                ->label('Expiration Date')
                ->minDate(fn() => now()),
        ]);
    }
}
