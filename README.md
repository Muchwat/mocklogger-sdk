The MockLogger SDK offers functionality for logging request and response data. This SDK provides methods to capture and log data pertaining to incoming HTTP requests and their associated responses.
 
Usage:
```
composer require moktech/mocklogger-sdk
php artisan vendor:publish --tag=mocklogger-config

use Moktech\MockLoggerSDK\MockLogger;

$logger = new MockLogger();
$logger->sendLog($request, $response);
```