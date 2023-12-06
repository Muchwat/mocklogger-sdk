<?php

namespace Moktech\MockLoggerSDK;

use Illuminate\Support\ServiceProvider;
use Moktech\MockLoggerSDK\Commands\Monitor;
use Moktech\MockLoggerSDK\Notifications\NotificationMail;

class MockloggerServiceProvider extends ServiceProvider
{   
    public function boot()
    {   
        $this->publishes([
            __DIR__.'/../config/mocklogger.php' => config_path('mocklogger.php'),
        ], 'mocklogger-config');

        $this->app->bind(NotificationMail::class, function ($app) {
            return $app->make(NotificationMail::class);
        });

        $this->commands([Monitor::class]);
    }

    public function register()
    {
        $this->app->singleton(MockLogger::class, function () {
            return new MockLogger();
        });
    }
}
