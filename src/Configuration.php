<?php
namespace Moktech\MockLoggerSDK;

/**
 * Configuration class for managing MockLogger SDK configuration settings.
 *
 * This class provides a convenient way to access configuration settings
 * such as the MockLogger application host URL, application ID, application key, and API token.
 * 
 * @method string getHostUrl()
 * @method string getAppId()
 * @method string getAppKey()
 * @method string getAppApiToken()
 */
class Configuration
{
    /**
     * @var Configuration|null The instance of the Configuration class.
     */
    private static ?Configuration $instance = null;

    /**
     * @var string $hostUrl The MockLogger Application host URL for the SDK.
     */
    protected string $hostUrl;

    /**
     * @var string $appId The application ID.
     */
    protected string $appId;

    /**
     * @var string $appKey The application key.
     */
    protected string $appKey;

    /**
     * @var string $appApiToken The API token for the application.
     */
    protected string $appApiToken;

    /**
     * Configuration constructor.
     *
     * Initializes the Configuration object by retrieving configuration values
     * from the mocklogger.php configuration file.
     */
    private function __construct()
    {
        $this->hostUrl = config('mocklogger.host_url');
        $this->appId = config('mocklogger.app_id');
        $this->appKey = config('mocklogger.app_key');
        $this->appApiToken = config('mocklogger.app_api_token');
    }

    /**
     * Get or create an instance of the Configuration class.
     *
     * @return Configuration The instance of the Configuration class.
     */
    public static function getInstance(): Configuration
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the host URL for the application.
     *
     * @return string The host URL.
     */
    protected function getHostUrl(): string
    {
        return $this->hostUrl;
    }

    /**
     * Get the application ID.
     *
     * @return string The application ID.
     */
    protected function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Get the application key.
     *
     * @return string The application key.
     */
    protected function getAppKey(): string
    {
        return $this->appKey;
    }

    /**
     * Get the API token for the application.
     *
     * @return string The API token.
     */
    protected function getAppApiToken(): string
    {
        return $this->appApiToken;
    }
}
