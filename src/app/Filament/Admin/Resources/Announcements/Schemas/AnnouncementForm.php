<?php

namespace App\Filament\Admin\Resources\Announcements\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->rows(8),

                Forms\Components\TextInput::make('redirect_url')
                    ->url()
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Publish')
                    ->helperText('Sekali dipublish, tanggal publikasi tidak akan berubah'),

                Forms\Components\DateTimePicker::make('expired_at')
                    ->nullable()
                    ->minDate(now())
                    ->label('Expiration Date'),
            ]);
    }
}
