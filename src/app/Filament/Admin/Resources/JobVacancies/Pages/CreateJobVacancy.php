<?php

namespace App\Filament\Admin\Resources\JobVacancies\Pages;

use App\Filament\Admin\Resources\JobVacancies\JobVacancyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobVacancy extends CreateRecord
{
    protected static string $resource = JobVacancyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (
            ($data['is_active'] ?? false) === true &&
            empty($data['published_at'])
        ) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
