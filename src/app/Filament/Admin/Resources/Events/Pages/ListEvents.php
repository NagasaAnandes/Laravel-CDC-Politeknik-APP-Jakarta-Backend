<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [

            CreateAction::make()
                ->label('Create Event')
                ->icon('heroicon-o-plus')

                // 🔥 UX: kasih konteks workflow
                ->tooltip(function () {
                    $user = Auth::user();

                    if (! $user) {
                        return null;
                    }

                    return $user->role->isAdmin()
                        ? 'Event will be automatically approved after creation'
                        : 'Event will be submitted for approval';
                })

                // 🔒 optional: restrict kalau perlu
                ->visible(fn() => Auth::check()),
        ];
    }
}
