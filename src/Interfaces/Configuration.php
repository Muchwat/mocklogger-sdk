<?php
namespace Moktech\MockLoggerSDK\Interfaces;

/**
 * The Configuration interface defines methods for retrieving
 * configuration settings required by the MockLogger SDK.
 */
interface Configuration
{
    /**
     * Get the MockLogger Application host URL for the SDK.
     *
     * @return string The host URL.
     */
    public function getHostUrl(): string;

    /**
     * Get the application ID.
     *
     * @return string The application ID.
     */
    public function getAppId(): string;

    /**
     * Get the application key.
     *
     * @return string The application key.
     */
    public function getAppKey(): string;

    /**
     * Get the API token for the application.
     *
     * @return string The API token.
     */
    public function getAppApiToken(): string;
}
