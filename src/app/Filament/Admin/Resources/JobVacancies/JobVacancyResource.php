<?php

namespace App\Filament\Admin\Resources\JobVacancies;

use App\Filament\Admin\Resources\JobVacancies\Pages\CreateJobVacancy;
use App\Filament\Admin\Resources\JobVacancies\Pages\EditJobVacancy;
use App\Filament\Admin\Resources\JobVacancies\Pages\ListJobVacancies;
use App\Filament\Admin\Resources\JobVacancies\Schemas\JobVacancyForm;
use App\Filament\Admin\Resources\JobVacancies\Tables\JobVacanciesTable;
use App\Models\JobVacancy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JobVacancyResource extends Resource
{
    protected static ?string $model = JobVacancy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return JobVacancyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobVacanciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobVacancies::route('/'),
            'create' => CreateJobVacancy::route('/create'),
            'edit' => EditJobVacancy::route('/{record}/edit'),
        ];
    }
}
