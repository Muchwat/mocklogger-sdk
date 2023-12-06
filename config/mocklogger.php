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
    
    // Configure server health monitor.
    'monitor' => [
        // Specify the web server used by your application, e.g., 'nginx' or 'apache2'.
        'web_server' => 'nginx', 

        'email' => [
            // Set the email address of the administrator. 
            // Leave as null if notifications are not required.
            'admin' => null,

            // Set time interval to get emails (minutes)
            'interval' => 30,

            // Set number of emails to be sent in a set time interval.
            'count'  => 4,
        ],

        // Configure thresholds for resources.
        'thresholds' => [
            // Set the CPU usage threshold (percentage).
            'cpu_usage' => env('MOCKLOGGER_CPU_THRESHOLD', 90),

            // Set the memory usage threshold (percentage). 
            'memory_usage' => env('MOCKLOGGER_MEMORY_THRESHOLD', 80),

            // Set the hard disk drive usage threshold (percentage).
            'hard_disk_space' => env('MOCKLOGGER_HDD_THRESHOLD', 80),
        ],
    ],
];
