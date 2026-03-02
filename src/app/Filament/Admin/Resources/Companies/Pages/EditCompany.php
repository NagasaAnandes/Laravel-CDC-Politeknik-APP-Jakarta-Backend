<?php

namespace App\Filament\Admin\Resources\Companies\Pages;

use App\Filament\Admin\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\UserRole;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $primaryUser = $this->record
            ->users()
            ->where('role', UserRole::COMPANY)
            ->first();

        if ($primaryUser) {
            $data['admin_name']  = $primaryUser->name;
            $data['admin_email'] = $primaryUser->email;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {

            // Update company profile
            $record->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'email_contact' => $data['email_contact'],
                'phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            $primaryUser = $record
                ->users()
                ->where('role', UserRole::COMPANY)
                ->first();

            if ($primaryUser) {

                $primaryUser->name  = $data['admin_name'];
                $primaryUser->email = $data['admin_email'];

                // Reset password only if filled
                if (! empty($data['admin_password'])) {
                    $primaryUser->password = Hash::make($data['admin_password']);
                }

                $primaryUser->save();
            }

            return $record;
        });
    }
}
