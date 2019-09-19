<?php

namespace KuznetsovZfort\Freshdesk;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use KuznetsovZfort\Freshdesk\Listeners\GetFreshdeskAgent;
use KuznetsovZfort\Freshdesk\Services\FreshdeskService;

class FreshdeskServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(FreshdeskService::class);
        $this->app->alias(FreshdeskService::FACADE_ACCESSOR, FreshdeskService::class);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/freshdesk.php' => config_path('freshdesk.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/freshdesk.php', 'freshdesk');

        Event::listen('Illuminate\Auth\Events\Login', GetFreshdeskAgent::class);
    }
}