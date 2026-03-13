<?php

namespace App\Filament\Admin\Resources\TracerSurveys\Pages;

use App\Filament\Admin\Resources\TracerSurveys\TracerSurveyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTracerSurvey extends EditRecord
{
    protected static string $resource = TracerSurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
