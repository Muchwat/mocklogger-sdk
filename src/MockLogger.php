<?php
namespace Moktech\MockLoggerSDK;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Client\Response as ClientResponse;

/**
 * MockLogger class for logging request and response data.
 *
 * This class provides methods to capture and log data related to incoming HTTP requests
 * and their corresponding responses. It inherits the HttpLogger class for 
 * sending logs to a remote server.
 * 
 * @method \Illuminate\Http\Client\Response sendLogData(Request $request)
 * @method \Illuminate\Http\Client\Response sendLog(Response $response)
 */
class MockLogger extends HttpLogger
{
    public function __construct()
    {   
        // Call the parent constructor and inject the instance of the Configuration class
        parent::__construct(Configuration::getInstance()); 
    }

    /**
     * Get request data to be logged.
     *
     * @param Request $request The incoming HTTP request.
     *
     * @return array An associative array containing request-related data.
     */
    private function request(Request $request): array
    {
        return [
            'user' => $request->user()->only(['name', 'email']),
            'ip_address' => $request->ip(),
            'full_url' => $request->fullUrl(),
            'route_name' => $request->route()->getName(),
            'method' => $request->method(),
            'payload' => $request->all(),
            'agent' => $request->userAgent(),
        ];
    }

    /**
     * Get response data to be logged.
     *
     * @param Response $response The HTTP response.
     *
     * @return array An associative array containing response-related data.
     */
    private function response(Response $response): array
    {
        return [
            'status_code' => $response->getStatusCode(),
            'content' => $response->getContent(),
            'format' => $response->headers->get('content-type'),
            'location' => $response->headers->get('location'),
        ];
    }

    /**
     * Log data to the remote server.
     *
     * @param array $data An associative array containing the data to be logged.
     * It should have the following structure:
     * [
     *     "request" => [
     *         'user' => [
     *             'name' => 'Kevin Muchwat',
     *             'email' => 'kevinmuchwat@gmail.com',
     *          ],
     *         'ip_address' => $request->ip(),
     *         'full_url' => $request->fullUrl(),
     *         'route_name' => $request->route()->getName(),
     *         'method' => $request->method(),
     *         'payload' => $request->all(),
     *         'agent' => $request->userAgent(),
     *     ],
     *     "response" => [
     *         'status_code' => $response->getStatusCode(),
     *         'content' => $response->getContent(),
     *         'format' => $response->headers->get('content-type'),
     *         'location' => $response->headers->get('location'),
     *     ],
     * ]
     * @return \Illuminate\Http\Client\Response
     */
    public function sendLogData(array $data = []): ClientResponse
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
    public function sendLog(Request $request, Response $response): ClientResponse
    {
        $data = [
            "request" => $this->request($request),
            "response" => $this->response($response),
        ];

        // Use logger to send logs to the server
        return $this->log($data);
    }
}
