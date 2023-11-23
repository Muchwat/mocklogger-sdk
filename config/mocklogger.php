<?php


return [
    // The base URL for the Mock Logger service.
    'host_url' => env('MOCKLOGGER_HOST_URL'),

    // The unique identifier for your application within the Mock Logger service.
    'app_id' => env('MOCKLOGGER_APP_ID'),

    // The secret key associated with your application in the Mock Logger service.
    'app_key' => env('MOCKLOGGER_APP_KEY'),

    // The API token required for authentication with the Mock Logger service.
    'app_api_token' => env('MOCKLOGGER_APP_API_TOKEN'),
];