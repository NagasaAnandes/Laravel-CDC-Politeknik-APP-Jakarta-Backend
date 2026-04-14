<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Domain\Approval\ApprovalService;
use App\Domain\Approval\Event\EventApprovalRules;
use Filament\Notifications\Notification;
use App\Enums\ApprovalStatus;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function beforeSave(): void
    {
        // ❗ Prevent editing approved event with registrations
        if (
            $this->record->approval_status === ApprovalStatus::APPROVED &&
            $this->record->registrations_count > 0
        ) {
            Notification::make()
                ->title('Cannot edit approved event with registrations')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $service = app(ApprovalService::class);
        $rules   = app(EventApprovalRules::class);

        try {
            DB::transaction(function () use ($user, $service, $rules) {

                $this->record->refresh();

                // 🔥 kalau bukan draft → revert ke draft
                if ($this->record->approval_status !== ApprovalStatus::DRAFT) {
                    $this->record = $service->revert(
                        model: $this->record,
                        actor: $user,
                        rules: $rules
                    );
                }

                // 🔥 auto flow seperti create
                if ($user->role->isAdmin()) {

                    $this->record = $service->submit(
                        model: $this->record,
                        actor: $user,
                        rules: $rules
                    );

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

            Notification::make()
                ->title('Event updated successfully')
                ->success()
                ->send();
        } catch (\Throwable $e) {

            report($e);

            Notification::make()
                ->title('Failed to update event')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
