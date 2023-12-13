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
     * Get the server's IP address
     *
     * @return string The  server's IP address
     */
    public static function getIpAddress(): ?string
    {
        // Execute the command to get the server's IP address
        $serverIpAddress = shell_exec('hostname -I');

        // The 'hostname -I' command usually returns a list of IP addresses; extract the first one
        $serverIpAddress = trim(explode(' ', $serverIpAddress)[0]);

        return $serverIpAddress;
    }

    /**
     * Get the status of the web server.
     *
     * @return string The status of the web server.
     */
    public static function getValue(): ?string
    {
        if (PHP_OS !== 'Linux') {
            return null;
        }

        // Get the configured web server name
        $webServerName = config('mocklogger.monitor.web_server');

        // Check the status of the web server
        return self::checkStatus($webServerName);
    }

    /**
     * Check the status of a specific web server.
     *
     * @param string $server The name of the web server.
     * @return string The status of the web server.
     */
    protected static function checkStatus(string $server): ?string
    {
        // Command to check the status of the web server using systemctl
        $command = "systemctl is-active $server.service";

        try {
            // Execute the command and trim the output
            return trim(shell_exec($command)) ?? null;
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            return null;
        }
    }
}
