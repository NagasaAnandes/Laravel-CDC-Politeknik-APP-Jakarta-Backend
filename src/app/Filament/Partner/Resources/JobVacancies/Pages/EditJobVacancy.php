<?php

namespace App\Filament\Partner\Resources\JobVacancies\Pages;

use App\Filament\Partner\Resources\JobVacancies\JobVacancyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Enums\ApprovalStatus;
use Illuminate\Support\Facades\Auth;

class EditJobVacancy extends EditRecord
{
    protected static string $resource = JobVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(
                    fn($record) =>
                    $record->approval_status === ApprovalStatus::DRAFT
                )
                ->action(function ($record) {

                    if ($record->approval_status !== ApprovalStatus::DRAFT) {
                        abort(403);
                    }

                    $record->delete();
                })
        ];
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        $user = Auth::user();

        if ($this->record->company_id !== $user->company_id) {
            abort(403);
        }

        if (! in_array($this->record->approval_status, [
            ApprovalStatus::DRAFT,
            ApprovalStatus::REJECTED,
        ], true)) {
            abort(403);
        }
    }
}
