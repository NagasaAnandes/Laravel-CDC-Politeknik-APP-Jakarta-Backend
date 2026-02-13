<?php

namespace App\Filament\Admin\Resources\Announcements\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                /* ======================
                 * TITLE
                 * ====================== */
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                /* ======================
                 * CATEGORY
                 * ====================== */
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

                /* ======================
                 * PRIORITY
                 * ====================== */
                TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

                /* ======================
                 * TARGET AUDIENCE
                 * ====================== */
                TextColumn::make('target_audience')
                    ->badge()
                    ->label('Audience')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

                /* ======================
                 * STATUS (VIRTUAL)
                 * ====================== */
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {

                        if (! $record->is_active) {
                            return 'Draft';
                        }

                        if ($record->expired_at && $record->expired_at->isPast()) {
                            return 'Expired';
                        }

                        return 'Published';
                    })
                    ->colors([
                        'gray'    => 'Draft',
                        'success' => 'Published',
                        'danger'  => 'Expired',
                    ]),

                /* ======================
                 * PUBLISH DATE
                 * ====================== */
                TextColumn::make('published_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                /* ======================
                 * EXPIRE DATE
                 * ====================== */
                TextColumn::make('expired_at')
                    ->dateTime('d M Y H:i')
                    ->toggleable(),
            ])

            ->filters([

                SelectFilter::make('category')
                    ->options([
                        'career'   => 'Career',
                        'academic' => 'Academic',
                        'event'    => 'Event',
                        'general'  => 'General',
                    ]),

                SelectFilter::make('priority')
                    ->options([
                        'normal'    => 'Normal',
                        'important' => 'Important',
                        'urgent'    => 'Urgent',
                    ]),

                SelectFilter::make('target_audience')
                    ->label('Audience')
                    ->options([
                        'student' => 'Student',
                        'alumni'  => 'Alumni',
                        'all'     => 'All',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])

            ->recordActions([
                EditAction::make(),
            ])

            ->defaultSort('published_at', 'desc');
    }
}
