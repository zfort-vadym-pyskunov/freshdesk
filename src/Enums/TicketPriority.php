<?php

namespace KuznetsovZfort\Freshdesk\Enums;

use KuznetsovZfort\PhpEnum\AbstractEnum;

class TicketPriority extends AbstractEnum
{
    const LOW = 1;
    const MEDIUM = 2;
    const HIGH = 3;
    const URGENT = 4;

    /**
     * @var array
     */
    public static $list = [
        self::LOW => 'Low',
        self::MEDIUM => 'Medium',
        self::HIGH => 'High',
        self::URGENT => 'Urgent',
    ];
}
