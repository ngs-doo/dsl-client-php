<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__ . '/HttpClient.php');

use NGS\Utils;
use NGS\Name;

/**
 * Proxy service to remote RPC-like API
 *
 * Remote services can be called using their name
 *
 * @package NGS\Client
 */
class ApplicationProxy extends BaseProxy
{
    const APPLICATION_URI = 'RestApplication.svc';

    /**
     * If remote service doesn't require any arguments it can be called using get method.
     *
     * @param        $command
     * @param array  $expectedCode
     * @param string $accept
     * @return mixed
     */
    public function get($command, array $expectedCode = array(200), $accept = 'application/json')
    {
        return
            $this->client->sendRequest(
                self::APPLICATION_URI.'/'.rawurlencode($command),
                'GET',
                null,
                $expectedCode,
                $accept);
    }

    /**
     * When remote service requires an argument, message with serialized payload will be sent.
     *
     * @param string $command
     * @param mixed  $data
     * @param array  $expectedCode
     * @param string $accept
     * @return mixed
     */
    public function post(
        $command,
        $data = null,
        array $expectedCode = array(200),
        $accept = 'application/json')
    {
        return
            $this->postJson(
                $command,
                $data !== null ? json_encode($data) : null,
                $expectedCode,
                $accept
            );
    }

    /**
     * As {@see post}, when arguments are already serialized in JSON
     *
     * @param  string $command
     * @param  string $data         JSON encoded data
     * @param  array  $expectedCode
     * @param  string $accept
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function postJson(
        $command,
        $data = null,
        array $expectedCode = array(200),
        $accept = 'application/json')
    {
        if(!is_string($data) && $data !== null)
            throw new \InvalidArgumentException('Data must be encoded in json string or null. Data was "'.\NGS\Utils\gettype($data).'"');
        return
            $this->client->sendRequest(
                self::APPLICATION_URI.'/'.rawurlencode($command),
                'POST',
                $data,
                $expectedCode,
                $accept);
    }
}
