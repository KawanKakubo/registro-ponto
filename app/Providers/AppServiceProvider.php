<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Route model binding: employees routes agora usam Person
        \Illuminate\Support\Facades\Route::model('employee', \App\Models\Person::class);
        \Illuminate\Support\Facades\Route::model('person', \App\Models\Person::class);
        \Illuminate\Support\Facades\Route::model('registration', \App\Models\EmployeeRegistration::class);
    }
}
