<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $actor = Auth::user();

        if (
            $this->record->id === $actor->id &&
            $data['role'] !== $actor->role->value
        ) {
            abort(403, 'You cannot change your own role.');
        }

        if (
            $this->record->role === \App\Enums\UserRole::SUPER_ADMIN &&
            $data['is_active'] === false
        ) {
            abort(403, 'Cannot deactivate super admin.');
        }

        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        if ($data['role'] !== \App\Enums\UserRole::COMPANY->value) {
            $data['company_id'] = null;
        }

        return $data;
    }
}
