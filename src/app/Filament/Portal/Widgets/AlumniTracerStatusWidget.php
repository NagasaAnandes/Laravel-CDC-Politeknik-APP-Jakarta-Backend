<?php

namespace App\Filament\Portal\Widgets;

use App\Models\TracerResponse;
use App\Models\TracerSurvey;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AlumniTracerStatusWidget extends BaseWidget
{
    protected static ?int $sort = 20;

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->isActive() === true
            && $user->isAlumni();
    }

    protected function getStats(): array
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $activeSurvey = TracerSurvey::query()
            ->where('is_active', true)
            ->latest('year')
            ->first();

        $hasSubmitted = $activeSurvey
            ? TracerResponse::query()
                ->where('survey_id', $activeSurvey->id)
                ->where('user_id', $user->id)
                ->exists()
            : false;

        return [
            Stat::make('Tracer Survey', $activeSurvey?->title ?? 'No active survey')
                ->description($activeSurvey?->year ? 'Year ' . $activeSurvey->year : 'Waiting for activation')
                ->color($activeSurvey ? 'primary' : 'gray'),

            Stat::make('Submission Status', $hasSubmitted ? 'Submitted' : 'Pending')
                ->description('Current active survey')
                ->color($hasSubmitted ? 'success' : 'warning'),
        ];
    }
}
