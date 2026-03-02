<?php

namespace App\Filament\Partner\Resources\JobVacancies;

use App\Filament\Partner\Resources\JobVacancies\Pages\CreateJobVacancy;
use App\Filament\Partner\Resources\JobVacancies\Pages\EditJobVacancy;
use App\Filament\Partner\Resources\JobVacancies\Pages\ListJobVacancies;
use App\Filament\Partner\Resources\JobVacancies\Schemas\JobVacancyForm;
use App\Filament\Partner\Resources\JobVacancies\Tables\JobVacanciesTable;
use App\Models\JobVacancy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if (! $user || ! $user->company_id) {
            abort(403);
        }

        return parent::getEloquentQuery()
            ->where('company_id', $user->company_id);
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
