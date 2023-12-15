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
     * @return array|null An array with free space, total space, and unit, or null if it couldn't be determined.
     */
    public static function getValue(): ?array
    {   
        if (OperatingSystem::isLinux()) {
            return self::getLinuxDiskSpace();
        } elseif (OperatingSystem::isWindows()) {
            return self::getWindowsDiskSpace();
        }

        return null;
    }

    /**
     * Get disk space information on Linux.
     *
     * @return array|null An array with free space, total space, and unit, or null if it couldn't be determined.
     */
    private static function getLinuxDiskSpace(): ?array
    {
        // Get total and free disk space on Linux
        $totalSpace = number_format((disk_total_space('/') / pow(1024, 3)), 2);
        $freeSpace = number_format((disk_free_space('/') / pow(1024, 3)), 2);

        // Format the result and return
        return [
            'free_space' => $freeSpace,
            'total_space' => $totalSpace,
            'unit' => 'GB',
        ];
    }

    /**
     * Get disk space information on Windows.
     *
     * @return array|null An array with free space, total space, and unit, or null if it couldn't be determined.
     */
    private static function getWindowsDiskSpace(): ?array
    {
        // Command to get disk space information using wmic on Windows
        $command = 'wmic logicaldisk get FreeSpace,Size,DriveType';

        // Execute the command and capture the output
        $output = shell_exec($command);

        // Extract FreeSpace, Size, and DriveType values from the output
        preg_match_all('/(\d+)/', $output, $matches);

        if (isset($matches[0][0], $matches[0][1])) {
            $freeSpace = $matches[0][0] / pow(1024, 3); // Convert to GB
            $totalSpace = $matches[0][1] / pow(1024, 3); // Convert to GB

            // Format the result and return
            return [
                'free_space' => number_format((float)$freeSpace, 2),
                'total_space' => number_format((float)$totalSpace, 2),
                'unit' => 'GB',
            ];
        }

        return null;
    }
}
