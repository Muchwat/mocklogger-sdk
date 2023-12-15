<?php

namespace Moktech\MockLoggerSDK\Services;

/**
 * Class SystemLoadMonitor
 * 
 * Monitor class for checking system load.
 */
class SystemLoadMonitor
{
    /**
     * Get the overall system load as a single value.
     *
     * @return string|null The overall system load value or null if it couldn't be determined.
     */
    public static function getValue(): ?string
    {   
        if (OperatingSystem::isLinux()) {
            return self::getLinuxSystemLoad();
        } elseif (OperatingSystem::isWindows()) {
            return self::getWindowsSystemLoad();
        }

        return null;
    }

    /**
     * Get the overall system load on Linux.
     *
     * @return string|null The overall system load value or null if it couldn't be determined.
     */
    private static function getLinuxSystemLoad(): ?string
    {
        // Get the system load average on Linux
        $loadAverage = sys_getloadavg();

        // Calculate the overall system load as the 1-minute load average
        $overallLoad = $loadAverage[0];

        // Format the result and return
        return sprintf('%.6f', $overallLoad);
    }

    /**
     * Get the overall system load on Windows.
     *
     * @return string|null The overall system load value or null if it couldn't be determined.
     */
    private static function getWindowsSystemLoad(): ?string
    {
        // Command to get system load information using wmic on Windows
        $command = 'wmic cpu get loadpercentage';

        // Execute the command and capture the output
        $output = shell_exec($command);

        // Extract the load percentage from the output
        preg_match('/(\d+)/', $output, $matches);

        if (isset($matches[1])) {
            $loadPercentage = $matches[1];

            // Return the load percentage
            return $loadPercentage;
        }

        return null;
    }
}
