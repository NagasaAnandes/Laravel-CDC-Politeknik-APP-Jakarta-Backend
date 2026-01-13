<?php

namespace App\Filament\Admin\Resources\JobVacancies\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class JobVacancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('company_name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('location')
                ->maxLength(100),

            Forms\Components\Select::make('employment_type')
                ->options([
                    'fulltime' => 'Full Time',
                    'parttime' => 'Part Time',
                    'intern' => 'Internship',
                    'remote' => 'Remote',
                ])
                ->required(),

            Forms\Components\Textarea::make('description')
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('external_apply_url')
                ->label('External Apply URL')
                ->url()
                ->required()
                ->maxLength(2048)
                ->placeholder('https://www.company.com/careers')
                ->helperText('Harus diawali dengan http:// atau https://'),

            Forms\Components\Toggle::make('is_active')
                ->default(true),

            Forms\Components\DateTimePicker::make('published_at'),

            Forms\Components\DatePicker::make('expired_at'),
        ]);
    }
}
