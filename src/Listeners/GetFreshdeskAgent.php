<?php

namespace KuznetsovZfort\Freshdesk\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use KuznetsovZfort\Freshdesk\Facades\Freshdesk;

class GetFreshdeskAgent
{
    /**
     * @param Login $event
     */
    public function handle(Login $event)
    {
        Log::debug('login', [$event->user->email]);
//        $agent = Freshdesk::getAgent($event->user->email);
//        if ($agent) {
//            Freshdesk::setCurrentUserAgentId($agent->id);
//        }
    }
}
