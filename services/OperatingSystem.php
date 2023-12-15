<?php

namespace Moktech\MockLoggerSDK\Services;

/**
 * Class OperatingSystem
 * 
 * Utility class for detecting the operating system.
 */
class OperatingSystem
{
    /**
     * Check if the current operating system is Linux.
     *
     * @return bool True if the operating system is Linux, false otherwise.
     */
    public static function isLinux(): bool
    {
        return (PHP_OS === 'Linux');
    }

    /**
     * Check if the current operating system is Windows.
     *
     * @return bool True if the operating system is Windows, false otherwise.
     */
    public static function isWindows(): bool
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }
}