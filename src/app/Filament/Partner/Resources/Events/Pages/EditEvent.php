<?php

namespace App\Filament\Partner\Resources\Events\Pages;

use Filament\Resources\Pages\EditRecord;
use App\Filament\Partner\Resources\Events\EventResource;
use App\Enums\ApprovalStatus;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset(
            $data['approval_status'],
            $data['is_active'],
            $data['published_at']
        );

        $data['start_datetime'] = \Carbon\Carbon::parse($data['start_datetime'])
            ->startOfDay();

        $data['end_datetime'] = \Carbon\Carbon::parse($data['end_datetime'])
            ->endOfDay();

        return $data;
    }

    protected function canEdit(): bool
    {
        return in_array(
            $this->record->approval_status,
            [
                \App\Enums\ApprovalStatus::DRAFT,
                \App\Enums\ApprovalStatus::REJECTED,
            ],
            true
        );
    }
}
