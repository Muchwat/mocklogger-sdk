<?php

namespace Moktech\MockLoggerSDK;

use Moktech\MockLoggerSDK\Commands\Monitor;
use Illuminate\Support\ServiceProvider;

class MockloggerServiceProvider extends ServiceProvider
{   
    public function boot()
    {   
        $this->publishes([
            __DIR__.'/../config/mocklogger.php' => config_path('mocklogger.php'),
        ], 'mocklogger-config');
    }

    public function register()
    {
        $this->app->singleton(MockLogger::class, function () {
            return new MockLogger();
        });

        // Register the command
        if ($this->app->runningInConsole()) {
            $this->commands([
                Monitor::class,
            ]);
        }
    }
}
