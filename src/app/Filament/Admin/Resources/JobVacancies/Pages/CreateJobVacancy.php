<?php

namespace App\Filament\Admin\Resources\JobVacancies\Pages;

use App\Filament\Admin\Resources\JobVacancies\JobVacancyResource;
use Filament\Resources\Pages\CreateRecord;
use App\Domain\Approval\ApprovalService;
use Illuminate\Support\Facades\Auth;
use App\Domain\Approval\Job\JobApprovalRules;

class CreateJobVacancy extends CreateRecord
{
    protected static string $resource = JobVacancyResource::class;

    protected function afterCreate(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $service = app(ApprovalService::class);
        $rules   = app(JobApprovalRules::class);

        if ($user->role->isAdmin()) {

            // Admin auto-submit then approve
            $service->submit(
                model: $this->record,
                actor: $user,
                rules: $rules
            );

            $service->approve(
                model: $this->record,
                actor: $user,
                rules: $rules
            );
        } else {

            $service->submit(
                model: $this->record,
                actor: $user,
                rules: $rules
            );
        }
    }
}
