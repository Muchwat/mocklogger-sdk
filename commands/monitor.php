<?php

namespace Moktech\MockLoggerSDK\Commands;

use Illuminate\Console\Command;
use Moktech\MockLoggerSDK\MockLogger;
use Moktech\MockLoggerSDK\Services\MonitorManagerService;
use Moktech\MockLoggerSDK\Services\CacheService;
use Moktech\MockLoggerSDK\Services\Throttler;
use Moktech\MockLoggerSDK\Services\Thresholds;

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
     * Throttler service instance.
     *
     * @var Throttler
     */
    protected $throttler;


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
            // Instantiate MockLogger, CacheService, Throttler and Thresholds
            $mockLogger = app(MockLogger::class);
            $this->cacheService = new CacheService();
            $this->throttler = new Throttler($this->cacheService);
            
            // Get monitor values from MonitorManagerService
            $monitorValues = MonitorManagerService::getValues();
            
            // For testing purposes, set all usage values to 100.
            $monitorValues['cpu_usage'] = 100;
            $monitorValues['memory_usage'] = 100;
            $monitorValues['hard_disk_space'] = [
                'free_space' => 100,
                'total_space' => 100,
                'unit' => 'GB',
            ];

            $this->thresholds = new Thresholds($monitorValues);

            // Check if resource usage exceeds thresholds
            if (!$this->thresholds->exceeded()) {
                $this->cacheService->reset();
            }

            $data = array_merge($monitorValues, [
                'thresholds_exceeded' => $this->thresholds->exceeded(),
                'can_send_email' => $this->throttler->canSendEmail(),
            ]);

            // Send log data to MockLogger
            $response = $mockLogger->sendLogData($data);

            $this->line('MockLogger Response Status Code: ' . $response->status());
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

}
