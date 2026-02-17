<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\JobVacancy;
use App\Observers\JobVacancyObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JobVacancy::observe(JobVacancyObserver::class);
    }
}
