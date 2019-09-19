<?php

namespace KuznetsovZfort\Freshdesk;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use KuznetsovZfort\Freshdesk\Facades\Freshdesk;
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

        Event::listen(Login::class, function (string $eventName, array $data) {
            Log::debug('login', [$data]);
//            $agent = Freshdesk::getAgent('$event->user->email');
//            if ($agent) {
//                Freshdesk::setCurrentUserAgentId($agent->id);
//            }
        });
    }
}