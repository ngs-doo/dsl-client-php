<?php
namespace NGS\Logger;

use Psr\Log\LoggerInterface;
use NGS\Client\HttpClient;

class PsrLoggerBridge
{
    protected $restHttp;

    public function __construct(HttpClient $restHttp = null)
    {
        $this->restHttp = ($restHttp === null) ? HttpClient::instance() : $restHttp;
    }

    public function add(LoggerInterface $logger)
    {
        $this->restHttp->addSubscriber(function ($event, $data) use (&$logger) {
            switch ($event) {
                case HttpClient::EVENT_REQUEST_BEFORE:
                    $http = $data['request'];
                    $http->logRequest($logger);
                    break;

                case HttpClient::EVENT_REQUEST_ERROR:
                    $logger->error($data['error']);
                    break;

                case HttpClient::EVENT_REQUEST_SENT:
                    $http = $data['request'];
                    $http->logResponse($logger);
                    break;
            }
        });
    }
}
