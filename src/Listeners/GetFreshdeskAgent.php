<?php

namespace KuznetsovZfort\Freshdesk\Listeners;

use Illuminate\Auth\Events\Login;
use KuznetsovZfort\Freshdesk\Facades\Freshdesk;

class GetFreshdeskAgent
{
    /**
     * @param Login $event
     */
    public function handle(Login $event)
    {
        $agent = Freshdesk::getAgent($event->user->email);
        if ($agent) {
            Freshdesk::setCurrentUserAgentId($agent->id);
        }
    }
}
