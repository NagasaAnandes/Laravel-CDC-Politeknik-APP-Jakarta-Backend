<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\DeleteAction;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            // DeleteAction::make()
            //     ->before(function ($record) {

            //         if (
            //             $record->role === \App\Enums\UserRole::SUPER_ADMIN &&
            //             \App\Models\User::where('role', 'super_admin')->count() <= 1
            //         ) {

            //             abort(403, 'Cannot delete last super admin.');
            //         }
            //     })
        ];
    }
}
