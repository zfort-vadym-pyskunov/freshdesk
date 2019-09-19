<?php

namespace KuznetsovZfort\Freshdesk\Facades;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;
use KuznetsovZfort\Freshdesk\Services\FreshdeskService;

/**
 * @method static bool|mixed getAgent(string $email)
 * @method static bool hasAgent(string $email)
 * @method static bool|mixed getNewTickets()
 * @method static bool isCurrentUserAgent()
 * @method static int|null getCurrentUserAgentId()
 * @method static int getTicketsCount(?int $status = null,?Carbon $from = null,?Carbon $to = null)
 * @method static bool|mixed apiCall(string $uri)
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