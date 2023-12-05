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
    public static function getValue(): string
    {
        // Command to calculate CPU usage percentage using /proc/stat
        $command = "grep 'cpu ' /proc/stat | awk '{print ($2+$4)*100/($2+$4+$5)}'";
        
        // Execute the command and capture the output
        $cpuUsage = shell_exec($command);

        // Format the result and return
        return number_format($cpuUsage, 2). '%';
    }
}
