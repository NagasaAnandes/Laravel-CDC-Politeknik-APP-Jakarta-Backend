<?php

namespace App\Filament\Admin\Resources\JobVacancies\Pages;

use App\Filament\Admin\Resources\JobVacancies\JobVacancyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobVacancy extends CreateRecord
{
    protected static string $resource = JobVacancyResource::class;
}
