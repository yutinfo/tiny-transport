<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // This app's UI is Bootstrap 4 (AdminLTE 3.1). Without this, Laravel's
        // default Tailwind paginator renders unstyled <svg class="w-5 h-5"> arrows
        // (Tailwind classes that don't exist here), which blow up to a giant icon.
        Paginator::useBootstrapFour();
    }
}
