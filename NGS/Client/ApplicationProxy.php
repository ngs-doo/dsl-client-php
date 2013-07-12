<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__.'/../Name.php');
require_once(__DIR__.'/RestHttp.php');

use NGS\Utils;
use NGS\Name;

/**
 * Proxy for executing server commands
 *
 * @package NGS\Client
 */
class ApplicationProxy
{
    const APPLICATION_URI = 'RestApplication.svc';

    protected $http;

    protected static $instance;

    /**
     * Create a new ApplicationProxy instance
     *
     * @param RestHttp $http RestHttp instance used for http request.
     * Optionally specify an instance, otherwise use a singleton instance
     */
    public function __construct(RestHttp $http = null)
    {
        $this->http = $http !== null ? $http : RestHttp::instance();
    }

    /**
     * Gets singleton instance of RestApplication.svc proxy
     *
     * @return ApplicationProxy
     */
    public static function instance()
    {
        if(self::$instance === null)
            self::$instance = new ApplicationProxy();
        return self::$instance;
    }

    /**
     * Execute server command via GET request
     *
     * @param        $command
     * @param array  $expectedCode
     * @param string $accept
     * @return mixed
     */
    public function get($command, array $expectedCode = array(200), $accept = 'application/json')
    {
        return
            $this->http->sendRequest(
                self::APPLICATION_URI.'/'.rawurlencode($command),
                'GET',
                null,
                $expectedCode,
                $accept);
    }

    /**
     * Execute server command via POST request
     *
     * @param string $command
     * @param array  $data
     * @param array  $expectedCode
     * @param string $accept
     */
    public function post(
        $command,
        array $data = null,
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
     * Execute server command via POST request
     * Use when sending data already encoded in JSON
     *
     * @param  string $command
     * @param  string $data         JSON encoded data
     * @param  array  $expectedCode
     * @param  string $accept
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
            $this->http->sendRequest(
                self::APPLICATION_URI.'/'.rawurlencode($command),
                'POST',
                $data,
                $expectedCode,
                $accept);
    }
}
