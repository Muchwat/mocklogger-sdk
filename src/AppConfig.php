<?php

/**
 * AppConfig class for managing application configuration settings.
 *
 * This class provides a convenient way to access configuration settings
 * such as the host URL, application ID, application key, and API token.
 *
 * Usage:
 * $config = new AppConfig();
 * $hostUrl = $config->getHostUrl();
 * $appId = $config->getAppId();
 * $appKey = $config->getAppKey();
 * $appApiToken = $config->getAppApiToken();
 */

namespace Moktech\MockLoggerSDK;

class AppConfig
{
    /**
     * @var string $hostUrl The host URL for the application.
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
     * AppConfig constructor.
     *
     * Initializes the AppConfig object by retrieving configuration values
     * from the Laravel configuration files.
     */
    public function __construct()
    {
        $this->hostUrl = config('mocklogger.host_url');
        $this->appId = config('mocklogger.app_id');
        $this->appKey = config('mocklogger.app_key');
        $this->appApiToken = config('mocklogger.app_api_token');
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
