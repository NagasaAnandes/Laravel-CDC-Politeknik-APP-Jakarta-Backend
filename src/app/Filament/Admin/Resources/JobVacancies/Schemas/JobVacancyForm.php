<?php

namespace App\Filament\Admin\Resources\JobVacancies\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;

class JobVacancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),

            /*
            |--------------------------------------------------------------------------
            | Company (Immutable After Create)
            |--------------------------------------------------------------------------
            */

            Forms\Components\Select::make('company_id')
                ->label('Company')
                ->relationship('company', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn($record) => $record !== null) // 🔒 immutable after create
                ->dehydrated(fn($record) => $record === null), // prevent update mutation

            Forms\Components\TextInput::make('location')
                ->maxLength(100),

            Forms\Components\Select::make('employment_type')
                ->options([
                    'fulltime' => 'Full Time',
                    'parttime' => 'Part Time',
                    'intern'   => 'Internship',
                    'remote'   => 'Remote',
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
                ->helperText('Arahkan ke halaman resmi perusahaan'),

            FileUpload::make('poster_path')
                ->label('Job Poster')
                ->image()
                ->disk('public')
                ->directory('job-posters')
                ->visibility('public')
                ->imagePreviewHeight('200')
                ->maxSize(2048)
                ->nullable(),

            /*
            |--------------------------------------------------------------------------
            | Read-Only Workflow Display
            |--------------------------------------------------------------------------
            */

            Section::make('Publication Status')
                ->schema([

                    Forms\Components\TextInput::make('approval_status_display')
                        ->label('Approval Status')
                        ->formatStateUsing(
                            fn($record) => $record?->approval_status?->label()
                        )
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('publish_status_display')
                        ->label('Published')
                        ->formatStateUsing(
                            fn($record) =>
                            $record?->isPublished() ? 'Yes' : 'No'
                        )
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('published_at_display')
                        ->label('Published At')
                        ->formatStateUsing(
                            fn($record) =>
                            $record?->published_at?->toDateTimeString() ?? '—'
                        )
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\DatePicker::make('expired_at')
                        ->label('Expire At')
                        ->helperText('Lowongan tidak tampil setelah tanggal ini')
                        ->minDate(now())
                        ->nullable()
                        ->rules([
                            'nullable',
                            'date',
                            'after_or_equal:today',
                        ]),
                ]),
        ]);
    }
}
