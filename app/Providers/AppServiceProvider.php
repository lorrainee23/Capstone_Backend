<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
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
    public function boot()
    {
        Relation::morphMap([
            'Admin'    => \App\Models\Admin::class,
            'Deputy'   => \App\Models\Deputy::class,
            'Enforcer' => \App\Models\Enforcer::class,
            'Head'     => \App\Models\Head::class,
        ]);
    }
}
