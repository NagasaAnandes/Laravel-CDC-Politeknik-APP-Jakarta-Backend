<?php

namespace App\Filament\Partner\Resources\JobVacancies\Pages;

use App\Filament\Partner\Resources\JobVacancies\JobVacancyResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Enums\ApprovalStatus;

class CreateJobVacancy extends CreateRecord
{
    protected static string $resource = JobVacancyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if (! $user || ! $user->company_id) {
            abort(403);
        }

        $data['company_id'] = $user->company_id;

        // 🔒 Force draft & block publish
        $data['approval_status'] = ApprovalStatus::DRAFT;
        $data['is_active'] = false;
        $data['published_at'] = null;
        $data['approved_at'] = null;
        $data['rejected_at'] = null;
        $data['rejection_reason'] = null;

        return $data;
    }
}
