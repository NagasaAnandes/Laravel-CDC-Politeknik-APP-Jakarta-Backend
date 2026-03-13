<?php

namespace App\Filament\Admin\Resources\TracerSurveys;

use App\Filament\Admin\Resources\TracerSurveys\RelationManagers\QuestionsRelationManager;
use App\Filament\Admin\Resources\TracerSurveys\Pages\CreateTracerSurvey;
use App\Filament\Admin\Resources\TracerSurveys\Pages\EditTracerSurvey;
use App\Filament\Admin\Resources\TracerSurveys\Pages\ListTracerSurveys;
use App\Filament\Admin\Resources\TracerSurveys\Schemas\TracerSurveyForm;
use App\Filament\Admin\Resources\TracerSurveys\Tables\TracerSurveysTable;
use App\Models\TracerSurvey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TracerSurveyResource extends Resource
{
    protected static ?string $model = TracerSurvey::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return TracerSurveyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TracerSurveysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTracerSurveys::route('/'),
            'create' => CreateTracerSurvey::route('/create'),
            'edit' => EditTracerSurvey::route('/{record}/edit'),
        ];
    }
}
