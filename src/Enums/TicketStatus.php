<?php

namespace KuznetsovZfort\Freshdesk\Enums;

use KuznetsovZfort\PhpEnum\AbstractEnum;

class TicketStatus extends AbstractEnum
{
    const OPEN = 2;
    const PENDING = 3;
    const RESOLVED = 4;
    const CLOSED = 5;

    /**
     * @var array
     */
    public static $list = [
        self::OPEN => 'OPEN',
        self::PENDING => 'PENDING',
        self::RESOLVED => 'RESOLVED',
        self::CLOSED => 'CLOSED',
    ];
}
