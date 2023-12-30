<?php

namespace Moktech\MockLoggerSDK;

use Illuminate\Support\ServiceProvider;
use Moktech\MockLoggerSDK\Commands\Monitor;
use Moktech\MockLoggerSDK\Middlewares\MockLoggerMiddleware;

class MockloggerServiceProvider extends ServiceProvider
{   
    public function boot()
    {   
        $this->publishes([
            __DIR__.'/../config/mocklogger.php' => config_path('mocklogger.php'),
        ], 'mocklogger-config');

        $this->commands([Monitor::class]);

        $this->app->booted(function () {
            $router = $this->app['router'];
            $router->middleware('mocklogger', MockLoggerMiddleware::class);
        });
    }

    public function register()
    {
        $this->app->singleton(MockLogger::class, function () {
            return new MockLogger();
        });
    }
}
