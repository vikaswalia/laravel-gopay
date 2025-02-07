<?php

return [
    'goid' => env('GOPAY_ID'),
    'clientId' => env('GOPAY_CLIENT_ID'),
    'clientSecret' => env('GOPAY_CLIENT_SECRET'),
    'defaultScope' => env('GOPAY_DEFAULT_SCOPE', 'ALL'),
    'gatewayUrl' => env('GOPAY_PRODUCTION_ENV', true) ? 
        'https://gate.gopay.cz/' : 'https://gw.sandbox.gopay.com/',
    'languages' => [ 
        'en' => 'ENGLISH',
        'cs' => 'CZECH',
        'sk' => 'SLOVAK',
    ],
    'timeout' => 30
];