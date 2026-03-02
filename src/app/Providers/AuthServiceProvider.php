<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\JobVacancy;
use App\Policies\JobVacancyPolicy;
use App\Models\Event;
use App\Policies\EventPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('access-admin-panel', function (User $user) {
            return $user->isAdmin() && $user->isActive();
        });

        Gate::define('manage-cdc-content', function (User $user) {
            return $user->isAdmin();
        });
    }

    protected $policies = [
        JobVacancy::class => JobVacancyPolicy::class,
        Event::class => EventPolicy::class,
        User::class => UserPolicy::class,
    ];
}
