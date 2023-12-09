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
            $mockLogger = app(MockLogger::class);

            $monitorValues = MonitorManagerService::getValues();

            $monitorValues['cpu_usage'] = 100;
            $monitorValues['memory_usage'] = 100;
            $monitorValues['hard_disk_space'] = [
                'freeSpace' => 100,
                'totalSpace' => 100,
                'unit' => 'GB',
            ];

            if ($this->exceedsThreshold($monitorValues)) {
                $hddSpace = $monitorValues['hard_disk_space'];
                $monitorValues['hard_disk_space'] = ($hddSpace['freeSpace'] / ($hddSpace['totalSpace'] ?? 0)) * 100;

                $this->sendNotificationEmail($monitorValues);
            } else {
                $this->resetCache();
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
            ($monitorValues['cpu_usage'] ?? 0) > $thresholds['cpu_usage'] ||
            ($monitorValues['memory_usage'] ?? 0) > $thresholds['memory_usage'] ||
            ($this->hddPercentage($monitorValues) ?? 0) > $thresholds['hard_disk_space']
        );
    }

    protected function sendNotificationEmail(array $monitorValues)
    {
        $adminEmail = Config::get('mocklogger.monitor.email.admin');

        if (!is_null($adminEmail)) {
            $appName = config('app.name');
            $emailCount = Config::get('mocklogger.monitor.email.count');
            $emailInterval = Config::get('mocklogger.email.throttle');

            $subject = "$appName - Server Resource Threshold Exceeded";

            $message = "Server ($appName) resources have exceeded predefined thresholds:\n" .
                "CPU: {$monitorValues['cpu_usage']}%\n" .
                "Memory: {$monitorValues['memory_usage']}%\n" .
                "Hard Disk: {$this->hddPercentage($monitorValues)}%";

            $this->sendEmailIfNeeded($adminEmail, $subject, $message, $emailCount, $emailInterval);
        }
    }

    protected function sendEmailIfNeeded($adminEmail, $subject, $message, $emailCount, $emailInterval)
    {
        if (Cache::get('mocklogger.email.throttle', false)) {
            $count = Cache::increment('mocklogger.sent.email.count');

            if ($count <= $emailCount) {
                Mail::raw($message, function ($message) use ($adminEmail, $subject) {
                    $message->to($adminEmail)->subject($subject);
                });
            }
        } else {
            $this->resetCache($emailInterval);
        }
    }

    protected function resetCache($emailInterval = null)
    {
        Cache::forget('mocklogger.sent.email.count');

        if (is_null($emailInterval)) {
            return Cache::forget('mocklogger.email.throttle');
        }

        Cache::put('mocklogger.email.throttle', true, now()->addMinutes($emailInterval));
    }

    protected function hddPercentage(array $monitorValues): float
    {
        $hddSpace = $monitorValues['hard_disk_space'];
        return ($hddSpace['freeSpace'] / ($hddSpace['totalSpace'] ?? 0)) * 100;
    }

    protected function outputResponseDetails($response)
    {
        $this->line('Response Status Code: ' . $response->status());
        $this->line('Response Body: ' . $response->body());
    }
}