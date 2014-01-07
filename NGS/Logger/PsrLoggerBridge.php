<?php
namespace NGS;

use Psr\Log\LoggerInterface;
use NGS\Client\RestHttp;

class PsrLoggerBridge
{
    protected $restHttp;

    public function __construct(RestHttp $restHttp = null)
    {
        $this->restHttp = ($restHttp === null) ? RestHttp::instance() : $restHttp;
    }

    public function add(LoggerInterface $logger)
    {
        $this->restHttp->addSubscriber(function ($event, $data) use (&$logger) {
            switch ($event) {
                case RestHttp::EVENT_REQUEST_BEFORE:
                    $http = $data['request'];
                    $http->logRequest($logger);
                    break;

                case RestHttp::EVENT_REQUEST_ERROR:
                    $logger->error($data['error']);
                    break;

                case RestHttp::EVENT_REQUEST_SENT:
                    $http = $data['request'];
                    $http->logResponse($logger);
                    break;
            }
        });
    }
}
