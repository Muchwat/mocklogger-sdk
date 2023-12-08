<?php

namespace Moktech\MockLoggerSDK\Commands;

use Illuminate\Console\Command;
use Moktech\MockLoggerSDK\MockLogger;
use Moktech\MockLoggerSDK\Services\MonitorManagerService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class Monitor extends Command
{
    protected $signature = 'mocklogger:monitor';
    protected $description = 'Monitor resource usages in your app!';

    public function handle()
    {
        try {
            $monitorValues = MonitorManagerService::getValues();
            $thresholdExceeded = $this->exceedsThreshold($monitorValues);

            if ($thresholdExceeded) {
                $monitorValues['hard_disk_space'] = $this->calculateHddPercentage($monitorValues['hard_disk_space']);
                $this->sendNotificationEmail($monitorValues);
            } else {
                $this->resetCache();
            }

            $appName = config('app.name');
            $response = $this->sendMockLoggerData($monitorValues, $appName);
            $this->outputResponseDetails($response);

            $this->info('Data sent successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    protected function exceedsThreshold(array $monitorValues): bool
    {
        $thresholds = Config::get('mocklogger.monitor.thresholds');
        return $this->cpuExceeded($monitorValues, $thresholds) ||
            $this->memoryExceeded($monitorValues, $thresholds) ||
            $this->hddExceeded($monitorValues, $thresholds);
    }

    protected function sendNotificationEmail(array $monitorValues)
    {
        $adminEmail = Config::get('mocklogger.monitor.email.admin');
        if (!is_null($adminEmail)) {
            $this->sendThresholdEmail($adminEmail, $monitorValues);
        }
    }

    protected function sendMockLoggerData(array $monitorValues, string $appName): array
    {
        $mockLogger = app(MockLogger::class);
        return $mockLogger->sendLogData([
            'monitor_values' => $monitorValues, 
            'app_name' => $appName],
        );
    }

    private function calculateHddPercentage(array $hddSpace): float
    {
        return ($hddSpace['freeSpace'] / ($hddSpace['totalSpace'] ?? 0)) * 100;
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
        return $this->calculateHddPercentage($monitorValues) > $thresholds['hard_disk_space'];
    }

    private function sendThresholdEmail(string $adminEmail, array $monitorValues): void
    {
        $appName = config('app.name');
        $message = $this->buildThresholdEmailMessage($monitorValues, $appName);
        $this->sendEmail($adminEmail, $appName, $message);
    }

    private function buildThresholdEmailMessage(array $monitorValues, string $appName): string
    {
        return "Server ($appName) resources have exceeded predefined thresholds:\n" .
            "CPU: {$monitorValues['cpu_usage']}%\n" .
            "Memory: {$monitorValues['memory_usage']}%\n" .
            "Hard Disk: {$this->calculateHddPercentage($monitorValues)}%";
    }

    private function sendEmail(string $adminEmail, string $appName, string $message): void
    {
        $subject = "$appName - Server Resource Threshold Exceeded";
        Mail::raw($message, function ($message) use ($adminEmail, $subject) {
            $message->to($adminEmail)->subject($subject);
        });
    }
}