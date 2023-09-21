The MockLogger SDK offers functionality for logging request and response data. This SDK provides methods to capture and log data pertaining to incoming HTTP requests and their associated responses.
 
Usage:
```
composer require moktech/mocklogger-sdk
php artisan vendor:publish --tag=mocklogger-config

use Moktech\MockLoggerSDK\MockLogger;

$logger = new MockLogger();
$logger->sendLog($request, $response);

or

$data = [
    "request" => [
        'user' => ['name' => 'Kevin Muchwat'],
        'ip_address' => $request->ip(),
        'full_url' => $request->fullUrl(),
        'method' => $request->method(),
        'payload' => $request->all(),
        'agent' => $request->userAgent(),
        'timestamp' => now(),
    ],
    "response" => [
        'status_code' => $response->getStatusCode(),
        'content' => $response->getContent(),
        'format' => $response->headers->get('content-type'),
        'location' => $response->headers->get('location'),
        'timestamp' => now(),
    ],
];

$logger->sendData($data);
```