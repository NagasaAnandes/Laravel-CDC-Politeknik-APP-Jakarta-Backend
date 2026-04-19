<?php

namespace App\Filament\Admin\Resources\JobVacancies\Pages;

use App\Filament\Admin\Resources\JobVacancies\JobVacancyResource;
use Filament\Resources\Pages\CreateRecord;
use App\Domain\Approval\ApprovalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
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

        try {
            DB::transaction(function () use ($user, $service, $rules) {

                // 🔄 reload record (hindari stale state)
                $this->record->refresh();

                if ($user->role?->isAdmin()) {

                    // submit dulu
                    $this->record = $service->submit(
                        model: $this->record,
                        actor: $user,
                        rules: $rules
                    );

                    // lalu approve
                    $this->record = $service->approve(
                        model: $this->record,
                        actor: $user,
                        rules: $rules
                    );
                } else {

                    $this->record = $service->submit(
                        model: $this->record,
                        actor: $user,
                        rules: $rules
                    );
                }
            });

            // 🔔 UX feedback
            Notification::make()
                ->title('Job created successfully')
                ->success()
                ->send();
        } catch (\Throwable $e) {

            report($e);

            Notification::make()
                ->title('Failed to process job workflow')
                ->body($e->getMessage())
                ->danger()
                ->send();

            // hentikan redirect / state Filament
            $this->halt();
        }
    }
}
