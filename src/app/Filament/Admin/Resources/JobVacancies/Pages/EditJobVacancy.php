<?php

namespace App\Filament\Admin\Resources\JobVacancies\Pages;

use App\Filament\Admin\Resources\JobVacancies\JobVacancyResource;
use Filament\Resources\Pages\EditRecord;

class EditJobVacancy extends EditRecord
{
    protected static string $resource = JobVacancyResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            $data['is_active'] &&
            empty($this->record->published_at) &&
            empty($data['published_at'])
        ) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
