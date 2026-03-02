<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $actor = Auth::user();

        if (
            $actor->role !== \App\Enums\UserRole::SUPER_ADMIN &&
            $data['role'] === \App\Enums\UserRole::SUPER_ADMIN->value
        ) {
            abort(403);
        }

        $data['password'] = bcrypt($data['password']);

        if ($data['role'] !== \App\Enums\UserRole::COMPANY->value) {
            $data['company_id'] = null;
        }

        return $data;
    }
}
