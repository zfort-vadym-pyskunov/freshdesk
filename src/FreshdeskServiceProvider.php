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
        $this->app->bind(FreshdeskService::class);
        $this->app->alias(FreshdeskService::class, FreshdeskService::FACADE_ACCESSOR);
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