<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Schemas\Schema;
use Filament\Forms;


class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('role')
                ->required()
                ->options([
                    UserRole::SUPER_ADMIN->value => 'Super Admin',
                    UserRole::ADMIN_CDC->value   => 'Admin CDC',
                    UserRole::STUDENT->value     => 'Student',
                    UserRole::ALUMNI->value      => 'Alumni',
                    UserRole::COMPANY->value     => 'Company',
                ])
                ->live(),

            Forms\Components\Select::make('company_id')
                ->relationship('company', 'name')
                ->visible(fn($get) => $get('role') === UserRole::COMPANY->value)
                ->required(fn($get) => $get('role') === UserRole::COMPANY->value),

            Forms\Components\Toggle::make('is_active')
                ->default(true),

            Forms\Components\TextInput::make('phone')
                ->tel()
                ->maxLength(30)
                ->nullable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null),

            Forms\Components\TextInput::make('linkedin_url')
                ->url()
                ->maxLength(255)
                ->nullable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null),

            Forms\Components\TextInput::make('graduation_year')
                ->numeric()
                ->rule('digits:4')
                ->minValue(1970)
                ->maxValue((int) now()->year)
                ->nullable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) $state : null),

            Forms\Components\TextInput::make('program_study')
                ->maxLength(255)
                ->nullable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null),

            Forms\Components\TextInput::make('password')
                ->password()
                ->required(fn(string $context) => $context === 'create')
                ->dehydrated(fn($state) => filled($state))
                ->helperText('Leave empty when editing to keep current password.'),
        ]);
    }
}
