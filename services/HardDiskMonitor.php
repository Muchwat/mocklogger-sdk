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
    public static function getValue(): string
    {
        // Get total and free disk space
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');

        // Calculate the percentage of available space
        $percentageAvailable = ($freeSpace / $totalSpace) * 100;

        // Format the result and return
        return number_format($percentageAvailable, 2) . '%';
    }
}