<?php

namespace Moktech\MockLoggerSDK\Services;

/**
 * Class CpuMonitor
 * 
 * Monitor class for checking CPU usage.
 */
class CpuMonitor
{ 
    /**
     * Get the CPU usage percentage.
     *
     * @return string The CPU usage percentage formatted with two decimal places.
     */
    public static function getValue(): ?string
    {   
        // Check the operating system and call the appropriate method
        if (OperatingSystem::isLinux()) {
            return self::getLinuxCpuUsage();
        } elseif (OperatingSystem::isWindows()) {
            return self::getWindowsCpuUsage();
        }

        return null;
    }

    /**
     * Get the CPU usage percentage on Linux.
     *
     * @return string The CPU usage percentage formatted with two decimal places.
     */
    private static function getLinuxCpuUsage(): ?string
    {
        // Command to calculate CPU usage percentage using /proc/stat
        $command = "grep 'cpu ' /proc/stat | awk '{print ($2+$4)*100/($2+$4+$5)}'";
        
        // Execute the command and capture the output
        $cpuUsage = shell_exec($command);

        // Format the result and return
        return number_format((float)$cpuUsage, 2). '%';
    }

    /**
     * Get the CPU usage percentage on Windows.
     *
     * @return string The CPU usage percentage formatted with two decimal places.
     */
    private static function getWindowsCpuUsage(): ?string
    {
        // Command to calculate CPU usage percentage using wmic
        $command = 'wmic cpu get loadpercentage';

        // Execute the command and capture the output
        $cpuUsage = shell_exec($command);

        // Format the result and return
        return number_format((float)$cpuUsage, 2). '%';
    }
}
