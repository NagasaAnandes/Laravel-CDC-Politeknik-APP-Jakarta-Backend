<?php

namespace App\Filament\Admin\Resources\TracerResponses;

use App\Filament\Admin\Resources\TracerResponses\RelationManagers\AnswersRelationManager;
use App\Filament\Admin\Resources\TracerResponses\Pages\CreateTracerResponse;
use App\Filament\Admin\Resources\TracerResponses\Pages\EditTracerResponse;
use App\Filament\Admin\Resources\TracerResponses\Pages\ListTracerResponses;
use App\Filament\Admin\Resources\TracerResponses\Schemas\TracerResponseForm;
use App\Filament\Admin\Resources\TracerResponses\Tables\TracerResponsesTable;
use App\Models\TracerResponse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TracerResponseResource extends Resource
{
    protected static ?string $model = TracerResponse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return TracerResponseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TracerResponsesTable::configure($table);
    }


    public static function getRelations(): array
    {
        return [
            AnswersRelationManager::class,
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTracerResponses::route('/'),
            'create' => CreateTracerResponse::route('/create'),
            'edit' => EditTracerResponse::route('/{record}/edit'),
        ];
    }
}
