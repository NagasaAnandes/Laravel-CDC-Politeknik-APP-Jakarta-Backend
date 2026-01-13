<?php

namespace App\Filament\Admin\Resources\JobVacancies\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn as DateColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

class JobVacanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_name')
                    ->sortable(),

                TextColumn::make('employment_type')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'fulltime' => 'Full-time',
                        'parttime' => 'Part-time',
                        'intern' => 'Intern',
                        'remote' => 'Remote',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->boolean(),

                DateColumn::make('expired_at')->date(),

                DateColumn::make('published_at')->since(),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
