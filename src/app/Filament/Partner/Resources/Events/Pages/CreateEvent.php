<?php

namespace App\Filament\Partner\Resources\Events\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Partner\Resources\Events\EventResource;
use Illuminate\Support\Facades\Auth;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Auth::user()->company_id;
        $data['approval_status'] = 'draft';
        $data['is_active'] = false;

        return $data;
    }
}
