<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class JobClicks extends Page
{
    protected string $view = 'filament.admin.pages.job-clicks';

    protected static ?string $navigationLabel = 'Job Clicks';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 62;
}
