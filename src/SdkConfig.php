<?php
namespace Moktech\MockLoggerSDK;
use Moktech\MockLoggerSDK\Interfaces\Configuration;

/**
 * SdkConfig class for managing MockLogger SDK configuration settings.
 *
 * This class provides a convenient way to access configuration settings
 * such as the MockLogger application host URL, application ID, application key, and API token.
 * 
 * @method string getHostUrl()
 * @method string getAppId()
 * @method string getAppKey()
 * @method string getAppApiToken()
 */
class SdkConfig implements Configuration
{
    /**
     * @var string $hostUrl The MockLogger Application host URL for the SDK.
     */
    private string $hostUrl;

    /**
     * @var string $appId The application ID.
     */
    private string $appId;

    /**
     * @var string $appKey The application key.
     */
    private string $appKey;

    /**
     * @var string $appApiToken The API token for the application.
     */
    private string $appApiToken;

    /**
     * SdkConfig constructor.
     *
     * Initializes the SdkConfig object by retrieving configuration values
     * from the mocklogger.php configuration file.
     */
    private function __construct()
    {
        $this->hostUrl = config('mocklogger.host_url');
        $this->appId = config('mocklogger.app_id');
        $this->appKey = config('mocklogger.app_key');
        $this->appApiToken = config('mocklogger.app_api_token');
    }

    // Create and return an instance of SdkConfig
    public static function create(): self
    {
        return new self();
    }

    /**
     * Get the host URL for the application.
     *
     * @return string The host URL.
     */
    public function getHostUrl(): string
    {
        return $this->hostUrl;
    }

    /**
     * Get the application ID.
     *
     * @return string The application ID.
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Get the application key.
     *
     * @return string The application key.
     */
    public function getAppKey(): string
    {
        return $this->appKey;
    }

    /**
     * Get the API token for the application.
     *
     * @return string The API token.
     */
    public function getAppApiToken(): string
    {
        return $this->appApiToken;
    }
}
