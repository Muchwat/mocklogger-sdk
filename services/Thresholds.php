<?php

namespace Moktech\MockLoggerSDK\Services;
use Illuminate\Support\Facades\Config;

/**
 * Class Resource Thresholds
 *
 * @package Moktech\MockLoggerSDK\Commands
 */
class Thresholds
{   

    /**
     * Cache service instance.
     *
     * @var array
     */
    private $monitorValues;

    /**
     * ResourceThresholds constructor.
     *
     * @param array $monitorValues
     */
    public function __construct(array $monitorValues)
    {
        $this->monitorValues = $monitorValues;
    }

    /**
     * Check if CPU usage exceeds the threshold.
     * @return bool
     */
    public function cpuExceeded(): bool
    {
        // Check if CPU usage exceeds threshold
        return ($this->monitorValues['cpu_usage'] ?? 0) > Config::get('mocklogger.monitor.thresholds.cpu_usage');
    }

    /**
     * Check if memory usage exceeds the threshold.
     *
     * @param array $monitorValues
     * @return bool
     */
    public function memoryExceeded(): bool
    {
        // Check if memory usage exceeds threshold
        return ($this->monitorValues['memory_usage'] ?? 0) > Config::get('mocklogger.monitor.thresholds.memory_usage');
    }

    /**
     * Check if HDD usage exceeds the threshold.
     *
     * @param array $monitorValues
     * @return bool
     */
    public function hddExceeded(): bool
    {
        // Check if HDD usage exceeds threshold
        return $this->hddPercentage() > Config::get('mocklogger.monitor.thresholds.hard_disk_space');
    }

    /**
     * Calculate HDD usage percentage.
     * @return float
     */
    public function hddPercentage(): float
    {
        // Calculate HDD percentage
        $hddSpace = $this->monitorValues['hard_disk_space'];
        $percentage = ($hddSpace['freeSpace'] / ($hddSpace['totalSpace'] ?? 0)) * 100;

        // Return the percentage rounded to 2 decimal places
        return (float)number_format($percentage, 2);
    }

    /**
     * Check if resource usage exceeds predefined thresholds.
     * @return bool
     */
    public function exceeds(): bool
    {
        // Check if any resource exceeds threshold
        return ($this->cpuExceeded() || $this->memoryExceeded() || $this->hddExceeded());
    }
}