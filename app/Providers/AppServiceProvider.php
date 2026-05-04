<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use App\Models\Appointment;
use App\Models\Session;
use App\Policies\AppointmentPolicy;
use App\Policies\SessionPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Register Policies
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Session::class, SessionPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();
    }
}