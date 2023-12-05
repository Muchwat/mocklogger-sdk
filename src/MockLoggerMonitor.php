<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Moktech\MockLoggerSDK\MockLogger;

class MockLoggerMonitor extends Command
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
    protected $description = 'Send data with HTTP to a URL using MockLogger';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // Instantiate MockLogger
            $mockLogger = app(MockLogger::class);

            // Prepare data to be sent
            $data = [
                // Your data here
            ];

            // Send data using MockLogger
            $response = $mockLogger->sendLogData($data);

            // Display success message
            $this->info('Data sent successfully.');

            // Optionally, you can output the response details if needed
            $this->line('Response Status Code: ' . $response->status());
            $this->line('Response Body: ' . $response->body());
        } catch (\Exception $e) {
            // Handle exceptions if any
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
