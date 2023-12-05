<?php

namespace Moktech\MockLoggerSDK\Services;

/**
 * Class WebServerMonitor
 * 
 * Monitor class for checking the status of a web server.
 */
class WebServerMonitor
{
    /**
     * Get the status of the web server.
     *
     * @return string The status of the web server.
     */
    public static function getValue(): string
    {
        // Get the configured web server name
        $webServerName = config('mocklogger.web_server');

        // Check the status of the web server
        return self::checkStatus($webServerName);
    }

    /**
     * Check the status of a specific web server.
     *
     * @param string $server The name of the web server.
     * @return string The status of the web server.
     */
    protected static function checkStatus(string $server): string
    {
        // Command to check the status of the web server using systemctl
        $command = "systemctl is-active $server.service";

        try {
            // Execute the command and trim the output
            return trim(shell_exec($command)) ?? 'Error: Unable to retrieve status';
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            return 'Error: Unable to execute command';
        }
    }
}
