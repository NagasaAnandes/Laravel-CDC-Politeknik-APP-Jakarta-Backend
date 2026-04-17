<?php

namespace App\Providers;

use App\Models\Certificate;
use App\Models\Education;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\JobVacancy;
use App\Policies\JobVacancyPolicy;
use App\Models\Event;
use App\Models\Experience;
use App\Policies\CertificatePolicy;
use App\Policies\EducationPolicy;
use App\Policies\EventPolicy;
use App\Policies\ExperiencePolicy;

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
        Experience::class => ExperiencePolicy::class,
        Education::class => EducationPolicy::class,
        Certificate::class => CertificatePolicy::class,
    ];
}
