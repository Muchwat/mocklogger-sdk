<?php
namespace Moktech\MockLoggerSDK;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Moktech\MockLoggerSDK\Interfaces\Configuration;

/**
 * HttpLogger class for logging data to a remote server using HTTP requests.
 *
 * This class allows you to send log data to a specified host URL using
 * Laravel's HTTP client. It requires an API key and other configuration
 * parameters provided by an instance of the SdkConfig class.
 * 
 * @method array getPerformanceInfo()
 * @method log(array $data)
 */
class HttpLogger
{
    /**
     * @var string $apiKey The API key used for authentication when sending log data.
     */
    protected $apiKey;

    /**
     * @var string $hostUrl The host URL to which log data will be sent.
     */
    protected $hostUrl;

    /**
     * @var string $appKey The application key used to identify the application.
     */
    protected $appKey;

    /**
     * HttpLogger constructor.
     *
     * Initializes the HttpLogger with configuration values from an instance of SdkConfig.
     *
     * @param Configuration $config An instance of SdkConfig containing configuration settings.
     */
    public function __construct(Configuration $config)
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
    protected function getPerformanceInfo(): array
    {
        $usage = getrusage();

        // CPU time used by the current script in seconds
        $cpu = $usage['ru_utime.tv_sec'] + ($usage['ru_utime.tv_usec'] / 1000000);

        // Maximum resident set size (memory usage) in kilobytes
        $memory = $usage['ru_maxrss'];

        return ["cpu" => $cpu, "memory" => $memory];
    }

    /**
     * Log data to the remote server.
     *
     * @param array $data The log data to be sent to the server.
     *
     * @throws \Exception If the API credentials are not set.
     * 
     * @return \Illuminate\Http\Client\Response=
     */
    private function log(array $data): Response
    {
        // Check if API key, app key, and host URL are set
        if (!$this->apiKey || !$this->appKey || !$this->hostUrl) {
            throw new \Exception('Environment variables must be set and valid!');
        }

        return Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
        ])->post("$this->hostUrl/api/log/track", [
            'data' => $data,
            'app_key' => $this->appKey,
            'usage' => $this->getPerformanceInfo(),
            'timestamp' => now(),
        ]);
    }
}
