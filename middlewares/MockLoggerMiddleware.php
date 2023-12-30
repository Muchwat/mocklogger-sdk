<?php

namespace Moktech\MockLoggerSDK\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Moktech\MockLoggerSDK\MockLogger;
use Symfony\Component\HttpFoundation\Response;

class MockLoggerMiddleware
{
    protected $logger;

    public function __construct(MockLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        try {
            $this->logger->sendLog($request, $response);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }
}
