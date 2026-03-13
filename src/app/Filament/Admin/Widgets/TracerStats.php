<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\TracerResponse;
use App\Models\TracerAnswer;

class TracerStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [

            Stat::make(
                'Total Responses',
                TracerResponse::count()
            ),

            Stat::make(
                'Alumni Working',
                TracerAnswer::where('answer_value', 'Saya bekerja')->count()
            ),

            Stat::make(
                'Seeking Job',
                TracerAnswer::where('answer_value', 'Saya sedang mencari pekerjaan')->count()
            ),

            Stat::make(
                'Continue Study',
                TracerAnswer::where('answer_value', 'Saya masih belajar/melanjutkan kuliah')->count()
            ),

        ];
    }
}
