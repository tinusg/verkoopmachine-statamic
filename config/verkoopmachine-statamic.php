<?php

return [
    'api_url' => env('VERKOOPMACHINE_API_URL', 'https://www.verkoopmachine.nl/api/v1'),
    'connect_timeout' => (int) env('VERKOOPMACHINE_CONNECT_TIMEOUT', 3),
    'timeout' => (int) env('VERKOOPMACHINE_TIMEOUT', 8),
    'cache_seconds' => (int) env('VERKOOPMACHINE_CACHE_SECONDS', 300),
    'stale_cache_seconds' => (int) env('VERKOOPMACHINE_STALE_CACHE_SECONDS', 86400),
];
