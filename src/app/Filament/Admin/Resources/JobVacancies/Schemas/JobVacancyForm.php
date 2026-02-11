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
                ->helperText('Arahkan ke halaman resmi perusahaan'),

            FileUpload::make('poster_path')
                ->label('Job Poster')
                ->image()
                ->disk('public')
                ->directory('job-posters')
                ->visibility('public')
                ->imagePreviewHeight('200')
                ->maxSize(2048)
                ->nullable()
                ->helperText('Poster lowongan (opsional). JPG / PNG / WEBP'),


            Section::make('Publication Settings')
                ->description('Pengaturan visibilitas lowongan ke mahasiswa')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->helperText('Jika nonaktif, lowongan tidak akan tampil di publik')
                        ->default(true),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Publish At')
                        ->helperText('Kosongkan untuk publish langsung'),

                    Forms\Components\DatePicker::make('expired_at')
                        ->label('Expire At')
                        ->helperText('Lowongan tidak akan tampil setelah tanggal ini'),
                ]),
        ]);
    }
}
