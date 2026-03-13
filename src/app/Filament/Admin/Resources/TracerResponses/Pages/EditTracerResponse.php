<?php

namespace App\Filament\Admin\Resources\TracerResponses\Pages;

use App\Filament\Admin\Resources\TracerResponses\TracerResponseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTracerResponse extends EditRecord
{
    protected static string $resource = TracerResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
