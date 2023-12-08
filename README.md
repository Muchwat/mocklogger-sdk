## MockLogger SDK Documentation
MockLogger SDK offers functionality for logging request and response data of a Laravel application. This SDK provides methods to capture and log data pertaining to incoming HTTP requests and their associated responses.

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

#### Register Service Provider:
Open the `config/app.php` file and add the service provider to the providers array:

```php
'providers' => [
    // ...
    Moktech\MockLoggerSDK\MockloggerServiceProvider::class,
],
```

#### Set Environment Variables:
Set the following environment variables in your application's `.env`. You can obtain these values from your Mocklogger application:

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

class TerminableMiddleware
{
    protected $logger;

    public function __construct(MockLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {  
        try {
            $this->logger->sendLog($request, $response);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
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
        'route_name' => $request->route()->getName(),
        'method' => $request->method(),
        'payload' => $request->all(),
        'agent' => $request->userAgent(),
    ],
    "response" => [
        'status_code' => $response->getStatusCode(),
        'content' => $response->getContent(),
        'format' => $response->headers->get('content-type'),
        'location' => $response->headers->get('location'),
    ],
];

$logger = new MockLogger();
$logger->sendData($data);
```

#### Server Health Monitoring
MockLogger SDK empowers you to maintain optimal server performance by allowing you to set usage limits on CPU, memory, and hard disk space. If any of these limits are exceeded, the SDK automatically sends a detailed log to administrator's email configured in `config/mocklogger.php`.

```php
return [
    ...
    // Configure server health monitor.
    'monitor' => [
        // Specify the web server used by your application, e.g., 'nginx' or 'apache2'.
        'web_server' => 'nginx', 

        // Set email configuarations, default is 4 emails per 30mins interval.
        'email' => [
            // Set the email address of the administrator. 
            // Leave as null if notifications are not required.
            'admin' => null, // e.g., kevinmuchwat@gmail.com

            // Set time interval to get emails (minutes), default is 30 minutes
            'interval' => 30,

            // Set number of emails to be sent in an interval, default is 4 emails.
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
]
```

To start monitoring your server, run this command:

```bash
php artisan mocklogger:monitor
```

For continuous monitoring, you can schedule this command with a cron job. 
Keep your server in check effortlessly with this tool.