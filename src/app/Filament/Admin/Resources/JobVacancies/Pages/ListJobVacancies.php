<?php

namespace App\Filament\Admin\Resources\JobVacancies\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Admin\Resources\JobVacancies\JobVacancyResource;
use App\Models\JobVacancy;
use Illuminate\Support\Facades\Gate;

class ListJobVacancies extends ListRecords
{
    protected static string $resource = JobVacancyResource::class;

    /*
    |--------------------------------------------------------------------------
    | Header Actions
    |--------------------------------------------------------------------------
    */

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Job')
                ->visible(fn() => Gate::allows('create', JobVacancy::class)),
        ];
    }
}
