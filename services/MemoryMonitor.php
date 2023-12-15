<?php

namespace Moktech\MockLoggerSDK\Services;

/**
 * Class MemoryMonitor
 * 
 * Monitor class for checking memory usage.
 */
class MemoryMonitor
{
    /**
     * Get the memory usage percentage.
     *
     * @return string|null The memory usage percentage or null if it couldn't be determined.
     */
    public static function getValue(): ?string
    {   
        if (OperatingSystem::isLinux()) {
            return self::getLinuxMemoryUsage();
        } elseif (OperatingSystem::isWindows()) {
            return self::getWindowsMemoryUsage();
        }

        return null;
    }

    /**
     * Get the memory usage percentage on Linux.
     *
     * @return string|null The memory usage percentage or null if it couldn't be determined.
     */
    private static function getLinuxMemoryUsage(): ?string
    {
        $command = "free | grep Mem | awk '{print $3/$2 * 100.0}'";
        $memoryUsage = shell_exec($command);

        return $memoryUsage !== null ? number_format($memoryUsage, 2) . '%' : null;
    }

    /**
     * Get the memory usage percentage on Windows.
     *
     * @return string|null The memory usage percentage or null if it couldn't be determined.
     */
    private static function getWindowsMemoryUsage(): ?string
    {
        // Command to calculate memory usage percentage using wmic
        $command = 'wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value';

        // Execute the command and capture the output
        $output = shell_exec($command);

        // Extract FreePhysicalMemory and TotalVisibleMemorySize values from the output
        preg_match_all('/(\d+)/', $output, $matches);
        
        if (isset($matches[0][0], $matches[0][1])) {
            $freeMemory = $matches[0][0];
            $totalMemory = $matches[0][1];

            // Calculate memory usage percentage
            $memoryUsage = ($totalMemory - $freeMemory) / $totalMemory * 100.0;

            return number_format($memoryUsage, 2) . '%';
        }

        return null;
    }
}
