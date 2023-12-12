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

    /* MonitorManagerService::getValues().
    *
    * @var $monitor
    */
    private $monitor;

    /* CPU threshold configuration.
    *
    * @var $cpuThreshold
    */
    private $cpuThreshold;

    /* Memory threshold configuration.
    *
    * @var $memoryThreshold
    */
    private $memoryThreshold;

    /* Hard disk space threshold configuration.
    *
    * @var hardDiskThreshold
    */
    private $hardDiskThreshold;



    /**
     * Thresholds constructor.
     *
     * @param MonitorManagerService::getValues()
     */

    public function __construct(array $monitor)
    {
        $this->monitor = $monitor;
        $this->cpuThreshold = Config::get('mocklogger.monitor.thresholds.cpu_usage');
        $this->memoryThreshold = Config::get('mocklogger.monitor.thresholds.memory_usage');
        $this->hardDiskThreshold = Config::get('mocklogger.monitor.thresholds.hard_disk_space');
    }

    /**
     * Check if CPU usage exceeds the threshold.
     * @return bool
     */
    private function cpuExceeded(): bool
    {
        // Check if CPU usage exceeds threshold
        return ($this->monitor['cpu_usage'] ?? 0) > $this->cpuThreshold;
    }

    /**
     * Check if memory usage exceeds the threshold.
     * @return bool
     */
    private function memoryExceeded(): bool
    {
        // Check if memory usage exceeds threshold
        return ($this->monitor['memory_usage'] ?? 0) > $this->memoryThreshold;
    }

    /**
     * Check if HDD usage exceeds the threshold.
     * @return bool
     */
    private function hddExceeded(): bool
    {
        // Check if HDD usage exceeds threshold
        return $this->hddPercentage() > $this->hardDiskThreshold;
    }

    /**
     * Calculate HDD usage percentage.
     * @return float
     */
    public function hddPercentage(): float
    {
        // Calculate HDD percentage
        $hddSpace = $this->monitor['hard_disk_space'];
        $percentage = ($hddSpace['free_space'] / ($hddSpace['total_space'] ?? 0)) * 100;

        // Return the percentage rounded to 2 decimal places
        return (float)number_format($percentage, 2);
    }

    /**
     * Check if resource usage exceeds predefined thresholds.
     * @return bool
     */
    public function exceeded(): bool
    {
        // Check if any resource exceeds threshold
        return ($this->cpuExceeded() || $this->memoryExceeded() || $this->hddExceeded());
    }
}
