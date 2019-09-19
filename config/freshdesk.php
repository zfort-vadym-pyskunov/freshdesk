<?php

return [
    'api_key' => env('FRESHDESK_API_KEY', ''),
    'api_url' => env('FRESHDESK_API_URL', 'https://{your_domain}.freshdesk.com/api/v2/'),
    'shared_secret' => env('FRESHDESK_SHARED_SECRET', ''),
    'sso_url' => env('FRESHDESK_SSO_URL', 'https://{your_domain}.freshdesk.com/login/sso/'),
    'tickets_url' => env('FRESHDESK_TICKETS_URL', 'https://matchbingo.freshdesk.com/a/tickets/filters/search'),
];
