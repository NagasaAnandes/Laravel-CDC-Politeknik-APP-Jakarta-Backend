<?php

namespace App\Filament\Admin\Resources\JobVacancies\Pages;

use App\Filament\Admin\Resources\JobVacancies\JobVacancyResource;
use Filament\Resources\Pages\EditRecord;

class EditJobVacancy extends EditRecord
{
    protected static string $resource = JobVacancyResource::class;
}
