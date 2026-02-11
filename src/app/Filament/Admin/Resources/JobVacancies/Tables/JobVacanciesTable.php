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
                    }),

                IconColumn::make('status')
                    ->label('Published')
                    ->state(fn($record) => $record->isPublished())
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime()
                    ->placeholder('Immediate'),

                TextColumn::make('expired_at')
                    ->label('Expires At')
                    ->date()
                    ->placeholder('—'),
            ])
            ->filters([
                // optional, jika mau
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
