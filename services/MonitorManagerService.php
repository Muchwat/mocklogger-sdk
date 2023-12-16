<?php

namespace Moktech\MockLoggerSDK\Services;

/**
 * Class MonitorManagerService
 * 
 * Manager class for collecting values from different monitors.
 */
class MonitorManagerService
{
    /**
     * Get values from various monitors.
     *
     * @return array Associative array containing values from different monitors.
     */
    public static function getValues(): array
    {
        return [
            'cpu_usage' => CpuMonitor::getValue(),
            'cpu_temperature' => CpuTemperatureMonitor::getValue(),
            'hard_disk_space' => HardDiskMonitor::getValue(),
            'memory_usage' => MemoryMonitor::getValue(),
            'system_load' => SystemLoadMonitor::getValue(),
            'web_server_status' => WebServerMonitor::getStatus(),
            'server_ip_address' => WebServerMonitor::getIpAddress(),
        ];
    }
}
