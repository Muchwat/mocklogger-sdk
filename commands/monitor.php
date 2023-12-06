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
        
        $thresholds['cpu'] = 20;
        $thresholds['memory'] = 20;
        $thresholds['hard_disk'] = 20;

        return (
            $monitorValues['cpu'] > $thresholds['cpu'] ||
            $monitorValues['memory'] > $thresholds['memory'] ||
            $monitorValues['hard_disk'] > $thresholds['hard_disk']
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
            $subject = "{$appName} - Server Resource Threshold Exceeded";
            
            // Customize the subject or email content as needed
            $message = "Server resources have exceeded predefined thresholds:\n" .
                       "CPU: {$monitorValues['cpu']}%\n" .
                       "Memory: {$monitorValues['memory']}%\n" .
                       "Hard Disk: {$monitorValues['hard_disk']}%";
            
            // Use Laravel's built-in mail functionality to send the email
            Mail::to($adminEmail)->send(new NotificationMail($subject, $message));
        }
    }
}