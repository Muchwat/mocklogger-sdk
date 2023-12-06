<?php

namespace Moktech\MockLoggerSDK\Commands;

use Illuminate\Console\Command;
use Moktech\MockLoggerSDK\MockLogger;
use Moktech\MockLoggerSDK\Services\MonitorManagerService;
use Moktech\MockLoggerSDK\Notifications\NotificationMail;
use Illuminate\Support\Facades\Mail;

class Monitor extends Command
{
    protected $signature = 'mocklogger:monitor';
    protected $description = 'Monitor resource usages in your app!';

    public function handle()
    {
        try {
            // Instantiate MockLogger
            $mockLogger = app(MockLogger::class);

            // Prepare monitor values to be sent
            $monitorValues = MonitorManagerService::getValues();

            // Check if any threshold is exceeded
            if ($this->exceedsThreshold($monitorValues)) {
                // Send email with appropriate subject
                $this->sendNotificationEmail($monitorValues);
            }

            // Send data using MockLogger
            $response = $mockLogger->sendLogData([
                'monitor_values' => $monitorValues,
            ]);

            // Optionally, you can output the response details if needed
            $this->line('Response Status Code: ' . $response->status());
            $this->line('Response Body: ' . $response->body());

            // Display success message
            $this->info('Data sent successfully.');
        } catch (\Exception $e) {
            // Handle exceptions if any
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Check if any threshold is exceeded.
     *
     * @param array $monitorValues
     * @return bool
     */
    protected function exceedsThreshold(array $monitorValues): bool
    {
        $thresholds = config('mocklogger.monitor.thresholds');

        $thresholds['cpu_usage'] = 95;
        $thresholds['memory_usage'] = 95;
        $thresholds['hard_disk_space'] = 95;

        return (
            $monitorValues['cpu_usage'] > $thresholds['cpu_usage'] ||
            $monitorValues['memory_usage'] > $thresholds['memory_usage'] ||
            $monitorValues['hard_disk_space'] > $thresholds['hard_disk_space']
        );
    }

    /**
     * Send email notification with appropriate subject.
     *
     * @param array $monitorValues
     */
    protected function sendNotificationEmail(array $monitorValues)
    {   
        $appName = config('app.name');
        $adminEmail = config('mocklogger.monitor.admin_email');

        if (!is_null($adminEmail)) {
            $subject = "$appName - Server Resource Threshold Exceeded";

            // Customize the subject or email content as needed
            $message = "Server ($appName) resources have exceeded predefined thresholds:\n" .
                       "CPU: {$monitorValues['cpu_usage']}%\n" .
                       "Memory: {$monitorValues['memory_usage']}%\n" .
                       "Hard Disk: {$monitorValues['hard_disk_space']}%";
            
            // Use Laravel's built-in mail functionality to send the email
            $mail = app(NotificationMail::class);
            $mail->subject($subject)->message($message)->to($adminEmail)->send();
        }
    }
}