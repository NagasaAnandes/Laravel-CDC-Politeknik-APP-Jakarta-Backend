<?php

namespace App\Filament\Partner\Resources\JobVacancies\Pages;

use App\Filament\Partner\Resources\JobVacancies\JobVacancyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Domain\Approval\ApprovalService;
use App\Enums\ApprovalStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ListJobVacancies extends ListRecords
{
    protected static string $resource = JobVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('submit')
                ->label('Submit')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(
                    fn($record) =>
                    $record->approval_status === ApprovalStatus::DRAFT
                )
                ->requiresConfirmation()
                ->action(function ($record) {

                    Gate::authorize('submit', $record);

                    app(ApprovalService::class)
                        ->submit($record, Auth::user());

                    Notification::make()
                        ->title('Job submitted for approval')
                        ->success()
                        ->send();
                }),
        ];
    }
}
