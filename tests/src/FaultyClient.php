<?php

use NGS\Client\HttpClient;

class FaultyClient extends HttpClient {

    public function sendRequest(
        $uriSegment,
        $method = 'GET',
        $body = null,
        array $expectedCode = null,
        $accept = 'application/json')
    {
        throw new ErrorException('FaultyClient was used');
    }
} 