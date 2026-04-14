<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;
use App\Domain\Approval\ApprovalService;
use Illuminate\Support\Facades\Auth;
use App\Domain\Approval\Event\EventApprovalRules;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function afterCreate(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $service = app(ApprovalService::class);
        $rules   = app(EventApprovalRules::class);

        try {
            DB::transaction(function () use ($user, $service, $rules) {

                // 🔄 reload record untuk safety
                $this->record->refresh();

                if ($user->role->isAdmin()) {

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
                ->title('Event created successfully')
                ->success()
                ->send();
        } catch (\Throwable $e) {

            report($e);

            Notification::make()
                ->title('Failed to process event workflow')
                ->body($e->getMessage())
                ->danger()
                ->send();

            // optional: rollback visual state
            $this->halt();
        }
    }
}
