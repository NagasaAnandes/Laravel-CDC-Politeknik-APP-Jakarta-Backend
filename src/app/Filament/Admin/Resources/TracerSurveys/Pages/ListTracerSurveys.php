<?php

namespace App\Filament\Admin\Resources\TracerSurveys\Pages;

use App\Filament\Admin\Resources\TracerSurveys\TracerSurveyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTracerSurveys extends ListRecords
{
    protected static string $resource = TracerSurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
