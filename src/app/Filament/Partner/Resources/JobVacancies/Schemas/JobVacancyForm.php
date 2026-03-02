<?php

namespace App\Filament\Partner\Resources\JobVacancies\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use App\Enums\ApprovalStatus;

class JobVacancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /*
            |--------------------------------------------------------------------------
            | Editable Only When Draft / Rejected
            |--------------------------------------------------------------------------
            */

            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->disabled(
                    fn($record) =>
                    $record &&
                        ! in_array(
                            $record->approval_status,
                            [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                            true
                        )
                ),

            Forms\Components\TextInput::make('location')
                ->maxLength(100)
                ->disabled(
                    fn($record) =>
                    $record &&
                        ! in_array(
                            $record->approval_status,
                            [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                            true
                        )
                ),

            Forms\Components\Select::make('employment_type')
                ->options([
                    'fulltime' => 'Full Time',
                    'parttime' => 'Part Time',
                    'intern'   => 'Internship',
                    'remote'   => 'Remote',
                ])
                ->required()
                ->disabled(
                    fn($record) =>
                    $record &&
                        ! in_array(
                            $record->approval_status,
                            [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                            true
                        )
                ),

            Forms\Components\Textarea::make('description')
                ->required()
                ->columnSpanFull()
                ->disabled(
                    fn($record) =>
                    $record &&
                        ! in_array(
                            $record->approval_status,
                            [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                            true
                        )
                ),

            Forms\Components\TextInput::make('external_apply_url')
                ->url()
                ->required()
                ->maxLength(2048)
                ->disabled(
                    fn($record) =>
                    $record &&
                        ! in_array(
                            $record->approval_status,
                            [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                            true
                        )
                ),

            FileUpload::make('poster_path')
                ->image()
                ->disk('public')
                ->directory('job-posters')
                ->visibility('public')
                ->nullable()
                ->disabled(
                    fn($record) =>
                    $record &&
                        ! in_array(
                            $record->approval_status,
                            [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                            true
                        )
                ),

            /*
            |--------------------------------------------------------------------------
            | Expiration — Editable Only Before Submission
            |--------------------------------------------------------------------------
            */

            Forms\Components\DatePicker::make('expired_at')
                ->label('Expire At')
                ->helperText('Lowongan tidak akan tampil setelah tanggal ini')
                ->minDate(today())
                ->nullable()
                ->disabled(
                    fn($record) =>
                    $record &&
                        ! in_array(
                            $record->approval_status,
                            [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                            true
                        )
                ),
        ]);
    }
}
