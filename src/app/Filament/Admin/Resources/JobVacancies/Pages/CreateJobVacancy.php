<?php

namespace App\Filament\Admin\Resources\JobVacancies\Pages;

use App\Filament\Admin\Resources\JobVacancies\JobVacancyResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateJobVacancy extends CreateRecord
{
    protected static string $resource = JobVacancyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::user()?->role === \App\Enums\UserRole::COMPANY) {
            $data['company_id'] = Auth::user()->company_id;
        }
        if (
            ($data['is_active'] ?? false) === true &&
            empty($data['published_at'])
        ) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
