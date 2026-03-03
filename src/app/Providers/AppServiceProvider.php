<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\JobVacancy;
use App\Observers\JobVacancyObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JobVacancy::observe(JobVacancyObserver::class);
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
