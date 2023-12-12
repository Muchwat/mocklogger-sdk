<?php

namespace Moktech\MockLoggerSDK\Services;

/**
 * Class HardDiskMonitor
 * 
 * Monitor class for checking available hard disk space.
 */
class HardDiskMonitor
{
    /**
     * Get the percentage of available hard disk space.
     *
     * @return string The percentage of available hard disk space formatted with two decimal places.
     */
    public static function getValue(): ?array
    {   
        if (PHP_OS !== 'Linux') { return null; }

        // Get total and free disk space
        $totalSpace = number_format((disk_total_space('/') / pow(1024, 3)), 2);
        $freeSpace = number_format((disk_free_space('/') / pow(1024, 3)), 2);

        // Format the result and return
        return [
            'free_space' => $freeSpace,
            'total_space' => $totalSpace,
            'unit' => 'GB',
        ];
    }
}