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
use Illuminate\Database\Eloquent\Builder;

class JobVacancyResource extends Resource
{
    protected static ?string $model = JobVacancy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Career Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Job Vacancy';
    protected static ?string $pluralModelLabel = 'Job Vacancies';

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
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation Badge (Pending Jobs)
    |--------------------------------------------------------------------------
    */

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('approval_status', 'submitted')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /*
    |--------------------------------------------------------------------------
    | Query Customization
    |--------------------------------------------------------------------------
    */

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->latest('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' => ListJobVacancies::route('/'),
            'create' => CreateJobVacancy::route('/create'),
            'edit' => EditJobVacancy::route('/{record}/edit'),
        ];
    }
}
