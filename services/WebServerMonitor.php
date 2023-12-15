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
     * Get the server IP address based on the operating system.
     *
     * @return string|null The server IP address or null if it couldn't be determined.
     */
    public static function getIpAddress(): ?string
    {
        if (OperatingSystem::isLinux()) {
            return self::getLinuxIpAddress();
        }

        if (OperatingSystem::isWindows()) {
            return self::getWindowsIpAddress();
        }

        return null;
    }

    /**
     * Get the server IP address on Windows.
     *
     * @return string|null The server IP address or null if it couldn't be determined.
     */
    private static function getWindowsIpAddress(): ?string
    {
        // Execute 'ipconfig' command to get IP address on Windows
        $output = shell_exec('ipconfig');

        // Extract IPv4 address from the output
        preg_match_all('/IPv4 Address[^:]*:\s*([^\r\n]+)/', $output, $matches);

        // Use the first match as the IP address
        $ipAddress = isset($matches[1][0]) ? trim($matches[1][0]) : null;

        return $ipAddress;
    }

    /**
     * Get the server IP address on Linux.
     *
     * @return string|null The server IP address or null if it couldn't be determined.
     */
    private static function getLinuxIpAddress(): ?string
    {
        // Execute 'hostname -I' command to get IP address on Linux
        $serverIpAddress = shell_exec('hostname -I');

        // The 'hostname -I' command usually returns a list of IP addresses; extract the first one
        $serverIpAddress = trim(explode(' ', $serverIpAddress)[0]);

        return $serverIpAddress;
    }

    /**
     * Check the status of a specific web server.
     *
     * @param string $server The name of the web server.
     * @return string The status of the web server.
     */
    protected static function checkStatus(string $server): ?string
    {
        if (PHP_OS !== 'Linux') {
            return null;
        }

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
}
