<?php

namespace Moktech\MockLoggerSDK\Commands;

use Illuminate\Console\Command;
use Moktech\MockLoggerSDK\MockLogger;

class Monitor extends Command
{
    protected $signature = 'mocklogger:monitor';
    protected $description = 'Monitor resource useges in your app!';

    public function handle()
    {
        try {
            // Instantiate MockLogger
            $mockLogger = app(MockLogger::class);

            // Prepare data to be sent
            $data = [
                'monitor' => 'test',
            ];

            // Send data using MockLogger
            $response = $mockLogger->sendLogData($data);

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
}
