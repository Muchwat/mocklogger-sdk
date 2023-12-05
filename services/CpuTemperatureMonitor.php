<?php

namespace Moktech\MockLoggerSDK\Services;

/**
 * Class CpuTemperatureMonitor
 * 
 * Monitor class for checking CPU temperature.
 */
class CpuTemperatureMonitor
{
    /**
     * Get the CPU temperature information.
     *
     * @return string The CPU temperature information.
     */
    public static function getValue(): ?string
    {   
        if (PHP_OS !== 'Linux') { return null; }

        // Execute the command to get CPU temperature using lm-sensors
        $temperature = shell_exec('sensors | grep "Core 0"');

        // You may need to adjust the command based on your system and sensor configuration
        // This example assumes that the temperature information is available for Core 0

        return $temperature;
    }
}
