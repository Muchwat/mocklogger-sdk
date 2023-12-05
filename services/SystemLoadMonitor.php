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
     * @return string The overall system load value.
     */
    public static function getValue(): string
    {
        // Get the system load average
        $loadAverage = sys_getloadavg();

        // Calculate the overall system load as the 1-minute load average
        $overallLoad = $loadAverage[0];

        // Format the result and return
        return sprintf('%.6f', $overallLoad);
    }
}
