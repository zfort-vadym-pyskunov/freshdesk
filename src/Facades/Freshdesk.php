<?php

namespace KuznetsovZfort\Freshdesk\Facades;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Facade;
use KuznetsovZfort\Freshdesk\Services\FreshdeskService;

/**
 * @method static mixed getAgent(string $email)
 * @method static mixed getContact(string $email)
 * @method static bool hasAgent(string $email)
 * @method static bool hasContact(string $email)
 * @method static mixed getNewTickets()
 * @method static bool isCurrentUserAgent()
 * @method static int|null getCurrentUserAgentId()
 * @method static void setCurrentUserAgentId(int $agentId)
 * @method static int getTicketsCount(?int $status = null,?Carbon $from = null,?Carbon $to = null)
 * @method static mixed apiCall(string $uri)
 * @method static string getSsoUrl(string $name, string $email, ?string $redirect = null)
 * @method static string getContactTicketsUrl(Authenticatable $user)
 *
 * @see FreshdeskService
 */
class Freshdesk extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return FreshdeskService::FACADE_ACCESSOR;
    }
}