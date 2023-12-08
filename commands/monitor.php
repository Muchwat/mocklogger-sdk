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
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Retrieve monitor values from the service
            $monitorValues = MonitorManagerService::getValues();

            // Check if any resource exceeds predefined thresholds
            $thresholdExceeded = $this->exceedsThreshold($monitorValues);

            if ($thresholdExceeded) {
                // Convert hard disk space to percentage and send notification email
                $monitorValues['hard_disk_space'] = $this->calculateHddPercentage($monitorValues['hard_disk_space']);
                $this->sendNotificationEmail($monitorValues);
            } else {
                // Reset cache if thresholds are not exceeded
                $this->resetCache();
            }

            // Get the application name from configuration
            $appName = config('app.name');

            // Send monitor data to the MockLogger
            $response = $this->sendMockLoggerData($monitorValues, $appName);

            // Output response details
            $this->outputResponseDetails($response);

            $this->info('Data sent successfully.');
        } catch (\Exception $e) {
            // Handle exceptions and display error message
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Check if any resource exceeds predefined thresholds.
     *
     * @param  array  $monitorValues
     * @return bool
     */
    protected function exceedsThreshold(array $monitorValues): bool
    {
        $thresholds = Config::get('mocklogger.monitor.thresholds');
        return $this->cpuExceeded($monitorValues, $thresholds) ||
            $this->memoryExceeded($monitorValues, $thresholds) ||
            $this->hddExceeded($monitorValues, $thresholds);
    }

    /**
     * Send monitor data to the MockLogger and return the response.
     *
     * @param  array  $monitorValues
     * @param  string $appName
     * @return array
     */
    protected function sendMockLoggerData(array $monitorValues, string $appName): array
    {
        $mockLogger = app(MockLogger::class);
        return $mockLogger->sendLogData([
            'monitor_values' => $monitorValues,
            'app_name' => $appName,
        ]);
    }

    /**
     * Calculate the percentage of free hard disk space.
     *
     * @param  array  $hddSpace
     * @return float
     */
    private function calculateHddPercentage(array $hddSpace): float
    {
        return ($hddSpace['freeSpace'] / ($hddSpace['totalSpace'] ?? 0)) * 100;
    }

    /**
     * Check if CPU usage exceeds the threshold.
     *
     * @param  array  $monitorValues
     * @param  array  $thresholds
     * @return bool
     */
    private function cpuExceeded(array $monitorValues, array $thresholds): bool
    {
        return ($monitorValues['cpu_usage'] ?? 0) > $thresholds['cpu_usage'];
    }

    /**
     * Check if memory usage exceeds the threshold.
     *
     * @param  array  $monitorValues
     * @param  array  $thresholds
     * @return bool
     */
    private function memoryExceeded(array $monitorValues, array $thresholds): bool
    {
        return ($monitorValues['memory_usage'] ?? 0) > $thresholds['memory_usage'];
    }

    /**
     * Check if hard disk space usage exceeds the threshold.
     *
     * @param  array  $monitorValues
     * @param  array  $thresholds
     * @return bool
     */
    private function hddExceeded(array $monitorValues, array $thresholds): bool
    {
        return $this->calculateHddPercentage($monitorValues) > $thresholds['hard_disk_space'];
    }

    /**
     * Send an email with resource threshold details.
     *
     * @param  string $adminEmail
     * @param  string $appName
     * @param  string $message
     * @return void
     */
    private function sendEmail(string $adminEmail, string $appName, string $message): void
    {
        $subject = "$appName - Server Resource Threshold Exceeded";
        Mail::raw($message, function ($message) use ($adminEmail, $subject) {
            $message->to($adminEmail)->subject($subject);
        });
    }

    /**
     * Send notification email with resource threshold details.
     *
     * @param  string $adminEmail
     * @param  array  $monitorValues
     * @return void
     */
    private function sendThresholdEmail(string $adminEmail, array $monitorValues): void
    {
        $appName = config('app.name');
        $message = $this->thresholdMessage($monitorValues, $appName);

        // Check if the email can be sent based on throttling settings
        if ($this->canSendEmail()) {
            $this->sendEmail($adminEmail, $appName, $message);
        }
    }

    /**
     * Build the email message with resource threshold details.
     *
     * @param  array  $monitorValues
     * @param  string $appName
     * @return string
     */
    private function thresholdMessage(array $monitorValues, string $appName): string
    {
        return "Server ($appName) resources have exceeded predefined thresholds:\n" .
            "CPU: {$monitorValues['cpu_usage']}%\n" .
            "Memory: {$monitorValues['memory_usage']}%\n" .
            "Hard Disk: {$monitorValues['hard_disk_space']}%";
    }

    /**
     * Check if the email sending is throttled.
     *
     * @param int $emailCount
     * @return bool
     */
    private function isEmailThrottled(int $emailCount): bool
    {
        return Cache::get('mocklogger.monitor.email.interval') ||
            Cache::get('email_count', 0) > $emailCount;
    }

    /**
     * Reset cache values for email throttling.
     *
     * @param  int|null  $emailInterval
     * @return void
     */
    private function resetCache(int $emailInterval = null): void
    {
        Cache::forget('email_count');
        Cache::forget('mocklogger.monitor.email.interval');

        if (!is_null($emailInterval)) {
            Cache::put('mocklogger.monitor.email.interval', true, now()->addMinutes($emailInterval));
        }
    }

    /**
     * Check if an email can be sent based on throttling settings.
     *
     * @return bool
     */
    private function canSendEmail(): bool
    {
        $emailCount = Config::get('mocklogger.monitor.email.count', 0);
        $emailInterval = Config::get('mocklogger.monitor.email.interval', 0);

        if (!$this->isEmailThrottled($emailCount)) {
            Cache::increment('email_count');
            return true;
        }

        $this->resetCache($emailInterval);
        return false;
    }

    /**
     * Send notification email if thresholds are exceeded.
     *
     * @param  array  $monitorValues
     * @return void
     */
    protected function sendNotificationEmail(array $monitorValues): void
    {
        $email = Config::get('mocklogger.monitor.email.admin');
        if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendThresholdEmail($email, $monitorValues);
        }
    }

    /**
     * Output details of the MockLogger response.
     *
     * @param  mixed  $response
     * @return void
     */
    private function outputResponseDetails($response): void
    {
        $this->line('Response Status Code: ' . $response->status());
        $this->line('Response Body: ' . $response->body());
    }
}
