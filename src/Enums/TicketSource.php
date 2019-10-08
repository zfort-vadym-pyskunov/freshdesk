<?php

namespace KuznetsovZfort\Freshdesk\Enums;

use KuznetsovZfort\PhpEnum\AbstractEnum;

class TicketSource extends AbstractEnum
{
    const EMAIL = 1;
    const PORTAL = 2;
    const PHONE = 3;
    const CHAT = 4;
    const MOBIHELP = 5;
    const FEEDBACK_WIDGET = 6;
    const OUTBOUND_EMAIL = 7;

    /**
     * @var array
     */
    public static $list = [
        self::EMAIL => 'Email',
        self::PORTAL => 'Portal',
        self::PHONE => 'Phone',
        self::CHAT => 'Chat',
        self::MOBIHELP => 'Mobihelp',
        self::FEEDBACK_WIDGET => 'Feedback Widget',
        self::OUTBOUND_EMAIL => 'Outbound Email',
    ];
}
