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
     * Get the status of the web server.
     *
     * @return string The status of the web server.
     */
    public static function getValue(): ?string
    {
        // Get the configured web server name
        $webServerName = config('mocklogger.monitor.web_server');

        // Check the status of the web server
        return self::getStatus($webServerName);
    }

    /**
     * Check the status of the web server.
     *
     * @param string $serviceName The name of the web server service.
     * @return string|null The status of the web server or null if it couldn't be determined.
     */
    public static function getStatus(string $serviceName): ?string
    {
        if (OperatingSystem::isLinux()) {
            return self::getLinuxWebServerStatus($serviceName);
        } 
        
        // if (OperatingSystem::isWindows()) {
        //     return self::getWindowsWebServerStatus($serviceName);
        // }

        return null;
    }

    /**
     * Check the status of the web server on Linux.
     *
     * @param string $serviceName The name of the web server service.
     * @return string|null The status of the web server or null if it couldn't be determined.
     */
    private static function getLinuxWebServerStatus(string $serviceName): ?string
    {
        // Command to check the status of the web server using systemctl
        $command = "systemctl is-active $serviceName.service";

        try {
            // Execute the command and trim the output
            return trim(shell_exec($command)) ?? null;
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            return null;
        }
    }

    /**
     * Check the status of the web server on Windows.
     *
     * @param string $serviceName The name of the web server service.
     * @return string|null The status of the web server or null if it couldn't be determined.
     */
    private static function getWindowsWebServerStatus(string $serviceName): ?string
    {
        // Command to check the status of the web server using sc
        $command = "sc query $serviceName";

        try {
            // Execute the command and capture the output
            $output = shell_exec($command);

            // Check if the service is running
            if (strpos($output, 'STATE') !== false && strpos($output, 'RUNNING') !== false) {
                return 'active';
            } elseif (strpos($output, 'STATE') !== false && strpos($output, 'STOPPED') !== false) {
                return 'inactive';
            } else {
                return null; // Unable to determine the status
            }
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            return null;
        }
    }
}
