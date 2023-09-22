## MockLogger SDK Documentation
The MockLogger SDK offers functionality for logging request and response data. This SDK provides methods to capture and log data pertaining to incoming HTTP requests and their associated responses.

With the MockLogger SDK, you can easily capture and log data from HTTP requests and responses, helping you monitor and analyze your application's interactions with external services and users actions.

### Installation
To get started with the MockLogger SDK, follow these installation steps:

#### Install the SDK:
Install the MockLogger SDK using Composer:

```bash
composer require moktech/mocklogger-sdk
```

#### Publish Configuration:
Publish the MockLogger SDK configuration file using Artisan:

```bash
php artisan vendor:publish --tag=mocklogger-config
```

#### Set Environment Variables:
Set the following environment variables in your application. You can obtain these values from your Mocklogger application:

```dotenv
MOCKLOGGER_HOST_URL=http://localhost:8000
MOCKLOGGER_APP_ID="My Application"
MOCKLOGGER_APP_KEY=c3ce75317d9c876d209a9f439b345345
MOCKLOGGER_APP_API_TOKEN=EomxCzUG0HFukdRWgKL26ThXuRstFTW
```

### Usage
Once you have installed the MockLogger SDK and configured your environment, you can now use it to log request and response data. Here are two ways to use the SDK:

#### Method 1: Using sendLog
Use this in your Terminable Middleware (Sunctum protected URL).

```php
use Moktech\MockLoggerSDK\MockLogger;


public function terminate(Request $request, Response $response)
{
    try {
        (new MockLogger())->sendLog($request, $response);
    } catch (\Throwable $th) {
        Log::info($th->getMessage());
    }
}
```

#### Method 2: Using sendData
You can also manually assemble the request and response data and use the sendData method to log it.

```php
use Moktech\MockLoggerSDK\MockLogger;

$data = [
    "request" => [
        'user' => [
            'name' => 'Kevin Muchwat',
            'email' => 'kevinmuchwat@gmail.com',
        ],
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

$logger = new MockLogger();
$logger->sendData($data);

```