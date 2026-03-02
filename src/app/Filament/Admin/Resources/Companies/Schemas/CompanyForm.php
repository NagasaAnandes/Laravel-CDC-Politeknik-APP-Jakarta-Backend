<?php

namespace App\Filament\Admin\Resources\Companies\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Company Profile
                |--------------------------------------------------------------------------
                */

                Section::make('Company Profile')
                    ->schema([

                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Company Logo')
                            ->image()
                            ->directory('company-logos')
                            ->disk('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->nullable()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(150)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(160),

                        Forms\Components\TextInput::make('industry')
                            ->maxLength(150),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email_contact')
                            ->email(),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(30),

                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | Primary Account
                |--------------------------------------------------------------------------
                */

                Section::make('Primary Account')
                    ->schema([

                        Forms\Components\TextInput::make('admin_name')
                            ->label('Account Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('admin_email')
                            ->label('Account Email')
                            ->email()
                            ->required()
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignoreRecord: true
                            ),

                        Forms\Components\TextInput::make('admin_password')
                            ->label('Password')
                            ->password()
                            ->required(fn(string $context) => $context === 'create')
                            ->dehydrated(false)
                            ->helperText('Required when creating. Leave empty when editing to keep current password.'),
                    ])
                    ->columns(2),

                Section::make('Logo')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Company Logo')
                            ->image()
                            ->directory('company-logos')
                            ->disk('public')
                            ->imageEditor()
                            ->maxSize(2048) // 2MB
                            ->nullable()
                            ->columnSpanFull(),
                    ])
            ]);
    }
}
