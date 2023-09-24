<?php
namespace Moktech\MockLoggerSDK;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Moktech\MockLoggerSDK\Configuration;

/**
 * HttpLogger class for logging data to a remote server using HTTP requests.
 *
 * This class allows you to send log data to a specified host URL using
 * Laravel's HTTP client. It requires an API key and other configuration
 * parameters provided by an instance of the Configuration class.
 * 
 * @method log(array $data)
 */
class HttpLogger
{
    /**
     * @var string $apiKey The API key used for authentication when sending log data.
     */
    private $apiKey;

    /**
     * @var string $hostUrl The host URL to which log data will be sent.
     */
    private $hostUrl;

    /**
     * @var string $appKey The application key used to identify the application.
     */
    private $appKey;

    /**
     * HttpLogger constructor.
     *
     * Initializes the HttpLogger with configuration values from an instance of Configuration.
     *
     * @param Configuration $config An instance of Configuration class.
     */
    protected function __construct(Configuration $config)
    {
        $this->apiKey = $config->getAppApiToken();
        $this->hostUrl = $config->getHostUrl();
        $this->appKey = $config->getAppKey();
    }

    /**
     * Get performance information about the current script.
     *
     * @return array An associative array containing CPU and memory usage information.
     */
    private function getResourceUsage(): array
    {
        $usage = getrusage();

        // CPU time used by the current script in seconds
        $cpu = $usage['ru_utime.tv_sec'] + ($usage['ru_utime.tv_usec'] / 1000000);
        
        // Maximum resident set size (memory usage) in kilobytes
        $memory = $usage['ru_maxrss'];

        // Peak memory usage in kilobytes
        $peakMemory = memory_get_peak_usage(true) / 1024;
        
        // Total execution time
        $executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

        return [
            "cpu" => $cpu,
            "memory" => $memory,
            'peak_memory' => $peakMemory,
            "execution_time" => $executionTime,
        ];
    }

    /**
     * Log data to the remote server.
     *
     * @param array $data The log data to be sent to the server.
     *
     * @throws \Exception If the API credentials are not set.
     * 
     * @return \Illuminate\Http\Client\Response
     */
    protected function log(array $data): Response
    {
        if (!isset($this->apiKey, $this->appKey, $this->hostUrl)) {
            throw new \Exception('Environment variables are not valid!');
        }

        return Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("$this->hostUrl/api/log/$this->appKey", [
            'data' => $data,
            'usage' => $this->getResourceUsage(),
            'timestamp' => now(),
        ]);
    }
}
