<?php

return [
    // Trust only the local ngrok agent or explicitly configured reverse proxies.
    'proxies' => array_values(array_filter(array_map('trim', explode(',', env('TRUSTED_PROXIES', ''))))),
];
