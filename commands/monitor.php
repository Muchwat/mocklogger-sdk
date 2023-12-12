<?php

namespace Moktech\MockLoggerSDK\Commands;

use Illuminate\Console\Command;
use Moktech\MockLoggerSDK\MockLogger;
use Moktech\MockLoggerSDK\Services\MonitorManagerService;
use Moktech\MockLoggerSDK\Services\CacheService;
use Moktech\MockLoggerSDK\Services\EmailThrottler;
use Moktech\MockLoggerSDK\Services\Thresholds;
use Illuminate\Support\Facades\Mail;
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
     * EmailThrottler service instance.
     *
     * @var EmailThrottler
     */
    protected $emailThrottler;


    /**
     * Thresholds service instance.
     *
     * @var thresholds
     */
    protected $thresholds;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Instantiate MockLogger, CacheService, EmailThrottler and Thresholds
            $mockLogger = app(MockLogger::class);
            $this->cacheService = new CacheService();
            $this->emailThrottler = new EmailThrottler($this->cacheService);
            
            // Get monitor values from MonitorManagerService
            $monitorValues = MonitorManagerService::getValues();
            
            // For testing purposes, set all usage values to 100.
            $monitorValues['cpu_usage'] = 100;
            $monitorValues['memory_usage'] = 100;
            $monitorValues['hard_disk_space'] = [
                'freeSpace' => 100,
                'totalSpace' => 100,
                'unit' => 'GB',
            ];

            $this->thresholds = new Thresholds($monitorValues);

            // Check if resource usage exceeds thresholds
            if ($this->thresholds->exceeds()) {
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

    protected function sendNotificationEmail(array $monitorValues): void
    {
        $adminEmail = Config::get('mocklogger.monitor.email.admin');

        // Validate admin email
        if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $appName = Config::get('app.name');

            // Check if email can be sent
            if ($this->emailThrottler->canSendEmail()) {
                $subject = "$appName - Server Resource Threshold Exceeded";

                $message = "Server ($appName) resources have exceeded predefined thresholds:\n" .
                    "CPU: {$monitorValues['cpu_usage']}%\n" .
                    "Memory: {$monitorValues['memory_usage']}%\n" .
                    "Hard Disk: {$this->thresholds->hddPercentage()}%";

                // Send email
                Mail::raw($message, function ($message) use ($adminEmail, $subject) {
                    $message->to($adminEmail)->subject($subject);
                });
            }
        }
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
