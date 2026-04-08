<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;
use App\Domain\Approval\ApprovalService;
use Illuminate\Support\Facades\Auth;
use App\Domain\Approval\Event\EventApprovalRules;

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

        if ($user->role->isAdmin()) {

            // ✅ WAJIB: submit dulu
            $service->submit(
                model: $this->record,
                actor: $user,
                rules: $rules
            );

            // baru approve
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
