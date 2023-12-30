<?php

namespace Moktech\MockLoggerSDK;

use Illuminate\Support\ServiceProvider;
use Moktech\MockLoggerSDK\Commands\Monitor;

class MockloggerServiceProvider extends ServiceProvider
{   
    public function boot()
    {   
        $this->publishes([
            __DIR__.'/../config/mocklogger.php' => config_path('mocklogger.php'),
        ], 'mocklogger-config');

        $this->commands([Monitor::class]);

        $this->app->booted(function () {
            $schedule = app('Illuminate\Console\Scheduling\Schedule');
            $schedule->command('mocklogger:monitor')->cron('1 * * * *');
        });
    }

    public function register()
    {
        $this->app->singleton(MockLogger::class, function () {
            return new MockLogger();
        });
    }
}
