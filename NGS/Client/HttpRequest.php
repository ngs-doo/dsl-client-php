<?php
namespace NGS\Client;

/**
 * Request object used by {@see NGS\Client\HttpClient}
 */
class HttpRequest
{
    private $uri;
    private $curl;
    private $method;
    private $options;
    private $responseInfo;
    private $responseHeaders;
    private $responseBody;
    private $responseTime;

    private function miliseconds()
    {
        $time = gettimeofday();
        return $time['sec']*1000 + (int) ($time['usec'] / 1000);
    }

    public function __construct($uri, $method = null, $body = null, $headers = null, $options = null)
    {
        $this->uri = $uri;
        $this->curl = curl_init($uri);

        $this->options = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLINFO_HEADER_OUT     => true,
            CURLOPT_HEADER          => true
        );

        if (is_array($options)) {
            $this->options += $options;
        }

        $this->method = $method;

        if($method !== null)
            $this->method($method);
        if($body !== null)
            $this->body($body);
        if($headers !== null)
            $this->headers($headers);
    }

    public function headers($headers)
    {
        if(!isset($this->options[CURLOPT_HTTPHEADER]))
            $this->options[CURLOPT_HTTPHEADER] = array();

        if(is_array($headers)) {
            foreach($headers as $key => $value)
                $this->options[CURLOPT_HTTPHEADER][] = $value;
        }
        else if(is_string($headers)) {
            $this->options[CURLOPT_HTTPHEADER][] = $headers;
        }
        return $this;
    }

    public function method($method)
    {
        $method = strtoupper($method);
        if ($method === 'POST') {
            $this->options[CURLOPT_POST] = true;
        } else {
            $this->options[CURLOPT_CUSTOMREQUEST] = $method;
        }
        return $this;
    }

    public function body($body)
    {
        $this->options[CURLOPT_POSTFIELDS] = $body;
    }

    public function send()
    {
        curl_setopt_array($this->curl, $this->options);

        $time = $this->miliseconds();
        $response = curl_exec($this->curl);
        $this->responseTime = $this->miliseconds() - $time;

        $this->responseInfo = curl_getinfo($this->curl);

        $headerSize = $this->responseInfo['header_size'];

        $this->responseHeaders = explode("\r\n", substr($response, 0, $headerSize));
        array_splice($this->responseHeaders, -2);

        $this->responseBody = substr($response, $headerSize);

        return $this->responseBody;
    }

    public function getResponseInfo()
    {
        return $this->responseInfo;
    }

    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    public function getResponseCode()
    {
        return isset($this->responseInfo['http_code']) ? $this->responseInfo['http_code'] : null;
    }

    public function getResponseContentType()
    {
        return isset($this->responseInfo['content_type']) ? $this->responseInfo['content_type'] : null;
    }

    public function getResponseTime()
    {
        return $this->responseTime;
    }

    public function getError()
    {
        $error = curl_error($this->curl);
        return $error ? $error : null;
    }

    public function __toString()
    {
        $headers = isset($this->options[CURLOPT_HTTPHEADER]) ? $this->options[CURLOPT_HTTPHEADER] : array();
        $body = isset($this->options[CURLOPT_POSTFIELDS]) ? $this->options[CURLOPT_POSTFIELDS] : '';
        return strtoupper($this->method).' '.$this->uri."\n"
            .implode("\n", $headers)."\n"
            .$body;
    }

    public function logRequest($logger)
    {
        $headers = $this->options[CURLOPT_HTTPHEADER];
        $body = isset($this->options[CURLOPT_POSTFIELDS]) ? $this->options[CURLOPT_POSTFIELDS] : '';
        $url = $this->method.' '.$this->uri;

        $logger->debug(
            'Sending HTTP Request:
{method} {uri}
{headers}
{body}',
            array(
                'method' => $this->method,
                'uri' => $this->uri,
                'headers' => implode(PHP_EOL, $headers),
                'body' => $body,
            )
        );
    }

    public function logResponse($logger)
    {
        $headers = $this->getResponseHeaders();
        $time = $this->getResponseTime();
        $body = $this->responseBody;

        $logger->info('{method} {uri}, {status}, {size} bytes, {time} ms', array(
            'method' => $this->method,
            'uri' => $this->uri,
            'status' => isset($headers[0]) ? $headers[0] : 'connection failed' ,
            'size' => strlen($body),
            'time' => $time
        ));
        $logger->debug(
            'Received HTTP Response:
{headers}
{body}',
            array(
                'headers' => implode(PHP_EOL, $headers),
                'body' => $body
            )
       );
    }
}
