<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;
use App\Domain\Approval\ApprovalService;
use App\Domain\Approval\Event\EventApprovalRules;
use Illuminate\Support\Facades\Auth;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function afterCreate(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        // Admin langsung approve
        if ($user->role->isAdmin()) {

            app(ApprovalService::class)->approve(
                model: $this->record,
                actor: $user,
                rules: app(EventApprovalRules::class)
            );

            return;
        }

        // Company submit
        app(ApprovalService::class)->submit(
            model: $this->record,
            actor: $user,
            rules: app(EventApprovalRules::class)
        );
    }
}
