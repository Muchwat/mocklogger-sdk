<?php

/**
 * MockLogger class for logging request and response data.
 *
 * This class provides methods to capture and log data related to incoming HTTP requests
 * and their corresponding responses. It utilizes an instance of the AppConfig class for
 * configuration and an instance of the HttpLogger class for sending logs to a remote server.
 *
 * Usage:
 * $logger = new MockLogger();
 * $logger->sendLog($request, $response);
 */

namespace Moktech\MockLoggerSDK;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MockLogger extends HttpLogger
{
    public function __construct()
    {
        parent::__construct(); // Call the parent constructor
    }

    /**
     * @var AppConfig $config An instance of AppConfig for configuration settings.
     */
    protected $config;

    /**
     * @var HttpLogger $logger An instance of HttpLogger for sending log data.
     */
    protected $logger;

    /**
     * Get request data to be logged.
     *
     * @param Request $request The incoming HTTP request.
     *
     * @return array An associative array containing request-related data.
     */
    public function requestData(Request $request): array
    {
        return [
            'user' => $request->user(),
            'ip_address' => $request->ip(),
            'full_url' => $request->fullUrl(),
            'method' => $request->method(),
            'payload' => $request->all(),
            'agent' => $request->userAgent(),
            'timestamp' => now(),
        ];
    }

    /**
     * Get response data to be logged.
     *
     * @param Response $response The HTTP response.
     *
     * @return array An associative array containing response-related data.
     */
    public function responseData(Response $response): array
    {
        return [
            'status_code' => $response->getStatusCode(),
            'content' => $response->getContent(),
            'format' => $response->headers->get('content-type'),
            'location' => $response->headers->get('location'),
            'timestamp' => now(),
        ];
    }

    /**
     * Log data to the remote server.
     *
     * @param array $data The data to be logged.
     * @return \Illuminate\Http\Client\Response
     */
    public function sendLogData(array $data = []): \Illuminate\Http\Client\Response
    {   
        return $this->log($data);
    }

    /**
     * Log request and response data.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response $response The HTTP response.
     * @return \Illuminate\Http\Client\Response
     */
    public function sendLog(Request $request, Response $response): \Illuminate\Http\Client\Response
    {
        $data = [
            "request" => $this->requestData($request),
            "response" => $this->responseData($response),
        ];

        // Use logger to send logs to the server
        return $this->log($data);
    }
}
