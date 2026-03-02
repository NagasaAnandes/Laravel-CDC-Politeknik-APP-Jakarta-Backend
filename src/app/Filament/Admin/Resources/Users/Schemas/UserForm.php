<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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

            Forms\Components\TextInput::make('password')
                ->password()
                ->required(fn(string $context) => $context === 'create')
                ->dehydrated(fn($state) => filled($state))
                ->helperText('Leave empty when editing to keep current password.'),
        ]);
    }
}
