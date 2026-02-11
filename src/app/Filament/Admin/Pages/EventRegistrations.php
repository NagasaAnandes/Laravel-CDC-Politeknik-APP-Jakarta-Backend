<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class EventRegistrations extends Page
{
    protected string $view = 'filament.admin.pages.event-registrations';

    protected static ?string $navigationLabel = 'Event Registrations';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 61;
}
