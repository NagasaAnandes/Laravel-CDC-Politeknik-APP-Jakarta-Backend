<?php

namespace App\Filament\Admin\Resources\TracerResponses\Pages;

use App\Filament\Admin\Resources\TracerResponses\TracerResponseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTracerResponses extends ListRecords
{
    protected static string $resource = TracerResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
