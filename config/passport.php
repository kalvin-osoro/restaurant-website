<?php

return [
    'guard' => 'web',
    'path' => 'api/v1/oauth',
    'middleware' => ['throttle:60,1'],
    'private_key' => env('PASSPORT_PRIVATE_KEY'),
    'public_key' => env('PASSPORT_PUBLIC_KEY'),
    'connection' => env('PASSPORT_CONNECTION'),
];
