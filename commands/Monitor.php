<?php

namespace Moktech\MockLoggerSDK\Commands;

use Moktech\MockLoggerSDK\MockLogger;
use Illuminate\Console\Command;

class Monitor extends Command
{
    protected $signature = 'mocklogger:monitor';
    protected $description = 'Send data with HTTP to a URL using MockLogger';

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


            // Output details of the direct POST request
            $this->line('Direct POST Request Status Code: ' . $response->status());
            $this->line('Direct POST Request Body: ' . $response->body());

            // Display success message
            $this->info('Data sent successfully.');
        } catch (\Exception $e) {
            // Handle exceptions if any
            $this->error('Error: ' . $e->getMessage());
        }
    }
}