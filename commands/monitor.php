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
    protected $signature = 'mocklogger:monitor';
    protected $description = 'Monitor resource usages in your app!';
    protected $cacheService;

    public function handle()
    {
        try {
            $mockLogger = app(MockLogger::class);
            $this->cacheService = new CacheService();

            $monitorValues = MonitorManagerService::getValues();

            // For testing purposes, you set all usage values to 100.
            $monitorValues['cpu_usage'] = 100;
            $monitorValues['memory_usage'] = 100;
            $monitorValues['hard_disk_space'] = [
                'freeSpace' => 100,
                'totalSpace' => 100,
                'unit' => 'GB',
            ];

            if ($this->exceedsThreshold($monitorValues)) {
                $this->sendNotificationEmail($monitorValues);
            } else {
                $this->cacheService->resetCache();
            }

            $response = $mockLogger->sendLogData(['monitor_values' => $monitorValues]);

            $this->outputResponseDetails($response);

            $this->info('Data sent successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    protected function exceedsThreshold(array $monitorValues): bool
    {
        $thresholds = Config::get('mocklogger.monitor.thresholds');

        return (
            $this->cpuExceeded($monitorValues, $thresholds) ||
            $this->memoryExceeded($monitorValues, $thresholds) ||
            $this->hddExceeded($monitorValues, $thresholds)
        );
    }

    private function cpuExceeded(array $monitorValues, array $thresholds): bool
    {
        return ($monitorValues['cpu_usage'] ?? 0) > $thresholds['cpu_usage'];
    }

    private function memoryExceeded(array $monitorValues, array $thresholds): bool
    {
        return ($monitorValues['memory_usage'] ?? 0) > $thresholds['memory_usage'];
    }

    private function hddExceeded(array $monitorValues, array $thresholds): bool
    {
        return $this->hddPercentage($monitorValues) > $thresholds['hard_disk_space'];
    }

    protected function hddPercentage(array $monitorValues): float
    {
        $hddSpace = $monitorValues['hard_disk_space'];
        return ($hddSpace['freeSpace'] / ($hddSpace['totalSpace'] ?? 0)) * 100;
    }

    protected function sendNotificationEmail(array $monitorValues)
    {
        $adminEmail = Config::get('mocklogger.monitor.email.admin');

        if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $appName = Config::get('app.name');
            $emailCount = Config::get('mocklogger.monitor.email.count');
            $emailInterval = Config::get('mocklogger.monitor.email.interval');

            $canSendEmail = $this->canSendEmail($emailCount, $emailInterval);

            if ($canSendEmail) {
                $subject = "$appName - Server Resource Threshold Exceeded";

                $message = "Server ($appName) resources have exceeded predefined thresholds:\n" .
                    "CPU: {$monitorValues['cpu_usage']}%\n" .
                    "Memory: {$monitorValues['memory_usage']}%\n" .
                    "Hard Disk: {$this->hddPercentage($monitorValues)}%";

                Mail::raw($message, function ($message) use ($adminEmail, $subject) {
                    $message->to($adminEmail)->subject($subject);
                });
            }
        }
    }

    protected function canSendEmail($emailCount, $emailInterval)
    {
        $count = $this->cacheService->increment(CacheService::EMAIL_COUNT_KEY);

        if ($this->isEmailSendingAllowed($count, $emailCount)) {
            return true;
        }

        $this->cacheService->resetCache($emailInterval);
        return false;
    }


    protected function isEmailSendingAllowed($count, $emailCount)
    {
        return !$this->cacheService->get(CacheService::EMAIL_THROTTLE_KEY, false) && $count <= $emailCount;
    }

    protected function outputResponseDetails($response)
    {
        $this->line('Response Status Code: ' . $response->status());
        $this->line('Response Body: ' . $response->body());
    }
}
