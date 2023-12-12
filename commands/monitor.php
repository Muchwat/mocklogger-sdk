<?php

namespace Moktech\MockLoggerSDK\Commands;

use Illuminate\Console\Command;
use Moktech\MockLoggerSDK\MockLogger;
use Moktech\MockLoggerSDK\Services\MonitorManagerService;
use Moktech\MockLoggerSDK\Services\CacheService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class Monitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mocklogger:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor resource usages in your app!';

    /**
     * Cache service instance.
     *
     * @var CacheService
     */
    protected $cacheService;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Instantiate MockLogger and CacheService
            $mockLogger = app(MockLogger::class);
            $this->cacheService = new CacheService();

            // Get monitor values from MonitorManagerService
            $monitorValues = MonitorManagerService::getValues();

            // For testing purposes, set usage values to 100
            $this->setTestUsageValues($monitorValues);

            // Check if resource usage exceeds thresholds
            if ($this->exceedsThreshold($monitorValues)) {
                // Send notification email
                $this->sendNotificationEmail($monitorValues);
            } else {
                // Reset cache if thresholds not exceeded
                $this->cacheService->resetCache();
            }

            // Send log data to MockLogger
            $response = $mockLogger->sendLogData(['monitor_values' => $monitorValues]);

            // Output response details
            $this->outputResponseDetails($response);

            $this->info('Data sent successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Set test usage values for monitoring.
     *
     * @param array $monitorValues
     * @return void
     */
    protected function setTestUsageValues(array &$monitorValues): void
    {
        // For testing purposes, set all usage values to 100.
        $monitorValues['cpu_usage'] = 100;
        $monitorValues['memory_usage'] = 100;
        $monitorValues['hard_disk_space'] = [
            'freeSpace' => 100,
            'totalSpace' => 100,
            'unit' => 'GB',
        ];
    }

    /**
     * Check if resource usage exceeds predefined thresholds.
     *
     * @param array $monitorValues
     * @return bool
     */
    protected function exceedsThreshold(array $monitorValues): bool
    {
        // Check if any resource exceeds threshold
        return (
            $this->cpuExceeded($monitorValues) ||
            $this->memoryExceeded($monitorValues) ||
            $this->hddExceeded($monitorValues)
        );
    }

    /**
     * Check if CPU usage exceeds the threshold.
     *
     * @param array $monitorValues
     * @return bool
     */
    private function cpuExceeded(array $monitorValues): bool
    {
        // Check if CPU usage exceeds threshold
        return ($monitorValues['cpu_usage'] ?? 0) > Config::get('mocklogger.monitor.thresholds.cpu_usage');
    }

    /**
     * Check if memory usage exceeds the threshold.
     *
     * @param array $monitorValues
     * @return bool
     */
    private function memoryExceeded(array $monitorValues): bool
    {
        // Check if memory usage exceeds threshold
        return ($monitorValues['memory_usage'] ?? 0) > Config::get('mocklogger.monitor.thresholds.memory_usage');
    }

    /**
     * Check if HDD usage exceeds the threshold.
     *
     * @param array $monitorValues
     * @return bool
     */
    private function hddExceeded(array $monitorValues): bool
    {
        // Check if HDD usage exceeds threshold
        return $this->hddPercentage($monitorValues) > Config::get('mocklogger.monitor.thresholds.hard_disk_space');
    }

    /**
     * Calculate HDD usage percentage.
     *
     * @param array $monitorValues
     * @return float
     */
    protected function hddPercentage(array $monitorValues): float
    {
        // Calculate HDD percentage
        $hddSpace = $monitorValues['hard_disk_space'];
        return ($hddSpace['freeSpace'] / ($hddSpace['totalSpace'] ?? 0)) * 100;
    }

    /**
     * Send notification email if resource thresholds are exceeded.
     *
     * @param array $monitorValues
     * @return void
     */
    protected function sendNotificationEmail(array $monitorValues): void
    {
        $adminEmail = Config::get('mocklogger.monitor.email.admin');

        // Validate admin email
        if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $appName = Config::get('app.name');
            $emailCount = Config::get('mocklogger.monitor.email.count');
            $emailInterval = Config::get('mocklogger.monitor.email.interval');

            // Check if email can be sent
            if ($this->canSendEmail($emailCount, $emailInterval)) {
                $subject = "$appName - Server Resource Threshold Exceeded";

                $message = "Server ($appName) resources have exceeded predefined thresholds:\n" .
                    "CPU: {$monitorValues['cpu_usage']}%\n" .
                    "Memory: {$monitorValues['memory_usage']}%\n" .
                    "Hard Disk: {$this->hddPercentage($monitorValues)}%";

                // Send email
                Mail::raw($message, function ($message) use ($adminEmail, $subject) {
                    $message->to($adminEmail)->subject($subject);
                });
            }
        }
    }

    /**
     * Check if an email can be sent based on count and interval.
     *
     * @param int $emailCount
     * @param int $emailInterval
     * @return bool
     */
    protected function canSendEmail(int $emailCount, int $emailInterval): bool
    {
        // Check if email sending is allowed
        if ($this->isEmailSendingAllowed($emailCount)) {
            return true;
        }

        // Reset cache and prevent email sending
        $this->cacheService->resetCache($emailInterval);
        return false;
    }

    /**
     * Check if email sending is allowed based on cache.
     *
     * @param int $emailCount
     * @return bool
     */
    protected function isEmailSendingAllowed(int $emailCount): bool
    {
        // Check if email sending is allowed based on cache
        $count = $this->cacheService->increment(CacheService::EMAIL_COUNT_KEY);
        return !$this->cacheService->get(CacheService::EMAIL_THROTTLE_KEY, false) && $count <= $emailCount;
    }

    /**
     * Output response details to the console.
     *
     * @param mixed $response
     * @return void
     */
    protected function outputResponseDetails($response): void
    {
        // Output response details
        $this->line('Response Status Code: ' . $response->status());
        $this->line('Response Body: ' . $response->body());
    }
}
