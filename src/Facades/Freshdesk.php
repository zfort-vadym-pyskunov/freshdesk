<?php

namespace KuznetsovZfort\Freshdesk\Facades;

use Illuminate\Support\Facades\Facade;
use KuznetsovZfort\Freshdesk\Services\FreshdeskService;

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