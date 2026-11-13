<?php

namespace App\Providers;

use App\Support\Access;
use App\Support\DummyData;
use Illuminate\Support\Facades\View;
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
        // The layout needs the current role, and the people each role can be shown as.
        View::composer('layouts.app', fn ($view) => $view->with([
            'me' => Access::me(),
            'roleOptions' => array_map(fn ($key) => DummyData::peopleFor($key), array_combine(array_keys(DummyData::ROLES), array_keys(DummyData::ROLES))),
        ]));
    }
}
