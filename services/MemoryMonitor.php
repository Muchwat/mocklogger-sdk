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
     * @return string The memory usage percentage formatted with two decimal places.
     */
    public static function getValue(): string
    {   
        $command = "free | grep Mem | awk '{print $3/$2 * 100.0}'";
        return number_format(shell_exec($command), 2) . '%';
    }
}
