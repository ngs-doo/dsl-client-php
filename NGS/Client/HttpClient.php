<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__.'/HttpRequest.php');
require_once(__DIR__.'/Exception/InvalidRequestException.php');
require_once(__DIR__.'/Exception/NotFoundException.php');
require_once(__DIR__.'/Exception/RequestException.php');
require_once(__DIR__.'/Exception/SecurityException.php');
require_once(__DIR__.'/Exception/ClientErrorException.php');
require_once(__DIR__.'/Exception/ServerErrorException.php');
require_once(__DIR__.'/../Converter/PrimitiveConverter.php');
require_once(__DIR__.'/../Converter/ObjectConverter.php');
require_once(__DIR__.'/QueryString.php');

use NGS\Client\Exception\InvalidRequestException;
use NGS\Client\Exception\NotFoundException;
use NGS\Client\Exception\RequestException;
use NGS\Client\Exception\SecurityException;
use NGS\Client\Exception\ServerErrorException;
use NGS\Client\Exception\ClientErrorException;
use NGS\Converter\PrimitiveConverter;
use NGS\Converter\ObjectConverter;

/**
 * HTTP client used for communication with platform
 * Should not be used directly, instead use domain patterns
 * Requests can be monitored via {@see addSubscriber}
 */
class HttpClient
{
    const EVENT_REQUEST_BEFORE    = 'request.before';
    const EVENT_REQUEST_SENT      = 'request.sent';
    const EVENT_REQUEST_ERROR     = 'request.error';

    protected $subscribers = array();

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $namespacePrefix;

    /**
     * @var string Authentication string
     */
    protected $auth;
    protected $certPath;

    /**
     * @var array
     */
    protected $lastResponse;

    /**
     * @var HttpClient Singleton instance
     */
    protected static $instance;

    /**
     * Creates new client instance
     *
     * @param string $apiUrl Service base url
     * @param string $username
     * @param string $password
     */
    public function __construct($apiUrl, $username=null, $password=null)
    {
        $this->apiUrl = $apiUrl;
        if ($username!==null && $password!==null) {
            $this->setAuth($username, $password);
        }
    }

    /**
     * Set username/password used for http authentication
     *
     * @param string $username
     * @param string $password
     */
    public function setAuth($username, $password)
    {
        $this->username = $username;
        $this->auth = 'Basic '.base64_encode($username.':'.$password);
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setCertificate($certPath) {
        $this->certPath = $certPath;
    }

    /**
     * Gets or sets singleton instance of rest Http
     *
     * @param HttpClient $http
     * @internal param \NGS\Client\HttpClient $client HttpClient instance
     * @return HttpClient
     */
    public static function instance(HttpClient $http = null)
    {
        if($http === null)
            return self::$instance;
        self::$instance = $http;
    }

    /**
     * Sends http request
     *
     * @param string $uriSegment   Appended to REST service uri
     * @param string $method       HTTP method
     * @param null   $body         Request body
     * @param array  $expectedCode Expected http codes, throw exception
     * @param string $accept
     * @throws Exception\InvalidRequestException|Exception\NotFoundException|Exception\RequestException|Exception\SecurityException
     * @throws Exception\RequestException
     * @internal param array $headers
     * @return mixed
     */
    public function sendRequest(
        $uriSegment,
        $method = 'GET',
        $body = null,
        array $expectedCode = null,
        $accept = 'application/json')
    {
        $options = array();
        if (isset($this->certPath)) {
            $options[CURLOPT_CAINFO] = $this->certPath;
        }

        $request = new HttpRequest($this->apiUrl.$uriSegment, $method, null, null, $options);

        $requestHeaders = array(
            'Accept: '.$accept,
            'Content-type: application/json',
            'Authorization: '.$this->auth,
            //'Content-length: 0'
        );
        if (($method==='PUT' || $method==='POST') && ($body===null || strlen($body)===0)){
            $requestHeaders[] = 'Content-length: 0';
        }

        $request->headers($requestHeaders);

        if($body !== null)
            $request->body($body);

        if ($this->hasSubscribers())
            $this->dispatch(self::EVENT_REQUEST_BEFORE, array(
                'request' => $request,
            ));

        $response = $request->send();

        $responseInfo = $request->getResponseInfo();
        $this->lastResponse = array(
            'info' => $responseInfo,
            'body' => $response
        );

        // no response received from server or curl errored out
        if($response === null && $this->hasSubscribers()) {
            $this->dispatch(self::EVENT_REQUEST_ERROR, array(
                'error' => $request->getError()
            ));
            $ex = new RequestException('Failed to send request. '.$request->getError());
            $ex->setRequest($request);
            throw $ex;
        }
        $httpCode = $request->getResponseCode();
        $contentType = $request->getResponseContentType();

        if ($this->hasSubscribers())
            $this->dispatch(self::EVENT_REQUEST_SENT, array(
                'request' => $request,
                'response' => array(
                    'body' => $response,
                    'code' => $httpCode,
                ),
                'curl_info' => $request->getResponseInfo(),
            ));

        if($expectedCode !== null && !in_array($httpCode, $expectedCode)) {
            switch($contentType) {
                case 'application/json':
                    $response = json_decode($response);
                    break;
                case 'text/xml':
                    $xml = new \SimpleXmlIterator($response);
                    $response = (string) $xml;
                    break;
            }
            $message = trim($response);
            if ($message==='') {
                $message = 'Unexpected http code. Response body was empty. ';
            }
            if ($curlError = $request->getError()) {
                $message .= 'Curl error: '.$curlError;
            }

            switch($httpCode) {
                case 400:
                    $ex = new InvalidRequestException($message, $httpCode);
                    break;
                case 401:
                case 403:
                    $ex = new SecurityException($message, $httpCode);
                    break;
                case 404:
                    $ex = new NotFoundException($message, $httpCode);
                    break;
                case 413:
                   $ex = new RequestException('Request body was too large. '.$message, $httpCode);
                   break;
                default:
                    if($httpCode < 300) {
                        $ex = new RequestException('Unexpected http code '.$httpCode.'. '.$message);
                    }
                    if ($httpCode>=400 && $httpCode < 500) {
                        $ex = new ClientErrorException($message, $httpCode);
                    }
                    if ($httpCode>=500 && $httpCode < 600) {
                        $ex = new ServerErrorException($message, $httpCode);
                    }
                    $ex = new RequestException($message, $httpCode);
                break;
            }
            $ex->setRequest($request);
            throw $ex;
        } else {
            return $response;
        }
    }

    public function parseResult($response, $class = null)
    {
        $data = json_decode($response, true);
        if($class !== null && is_array($data)) {
            $converter = ObjectConverter::getConverter($class);
            return $converter::fromJson($response, false, $this);
        }
        return $data;
    }

    public function getLastResult()
    {
        return $this->lastResponse;
    }

    /**
     * Subscribe a callable to listen to HTTP request events
     *
     * Example use for simple logging:<br>
     * <code>
     * $http = HttpClient::instance();
     * $http->addSubscriber(function($event, $context) {
     *     if ($event === HttpClient::EVENT_REQUEST_SENT) {
     *         echo 'request sent';
     *         print_r($context);
     *     }
     * });
     * </code>
     *
     * @param callable $subscriber
     * @throws \InvalidArgumentException
     */
    public function addSubscriber($subscriber)
    {
        if (!is_callable($subscriber)) {
            throw new \InvalidArgumentException('Subscriber must be callable type!');
        }
        $this->subscribers[] = $subscriber;
    }

    /**
     * Dispatches event to all subscribed listeners
     *
     * @param       $event
     * @param array $context
     */
    protected function dispatch($event, array $context)
    {
        array_map(
            function($subscriber) use ($event, $context) {
                call_user_func_array($subscriber, array($event, $context));
            },
            $this->subscribers);
    }

    private function hasSubscribers()
    {
        return !empty($this->subscribers);
    }

    /**
     * Set namespace prefix of generated modules
     *
     * @param null $prefix
     * @throws \InvalidArgumentException
     * @internal param string $namespace
     */
    public function setNamespacePrefix($prefix=null)
    {
        if (!is_string($prefix)) {
            throw new \InvalidArgumentException("Namespace prefix must be a string ");
        }
        if (strlen($prefix === '')) {
            $this->namespacePrefix = null;
        }
        if ($prefix[0] === '\\') {
            $prefix = substr($prefix, 1);
        }
        $this->namespacePrefix = $prefix;
    }

    /**
     * Get namespace prefix of generated modules
     *
     * @return string
     */
    public function getNamespacePrefix()
    {
        return $this->namespacePrefix;
    }

    /**
     * Gets DSL name from class or object instance
     *
     * @param string|object $name Fully qualified class name or object instance
     * @throws \InvalidArgumentException
     * @return string DSL name
     */
    public function getDslName($name)
    {
        if (is_object($name)) {
            $name = get_class($name);
        }
        elseif(!is_string($name)) {
            throw new \InvalidArgumentException('Invalid type for name, name was not string');
        }
        if (!strlen($name)) {
            throw new \InvalidArgumentException('Name cannot be an empty string');
        }
        if (static::isDslName($name)) {
            return $name;
        }
        if ($name[0] === '\\') {
            $name = substr($name, 1);
        }
        if ($this->namespacePrefix) {
            $name = substr($name, strlen($this->namespacePrefix));
        }
        return str_replace('\\', '.', $name);
    }

    /**
     * Gets class name from DSL name
     *
     * @param string $name Fully qualified class name or object instance
     * @throws \InvalidArgumentException
     * @return string DSL name
     */
    public function getClassName($name)
    {
        if (is_object($name)) {
            return get_class($name);
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Invalid type for name, name was not a string or an object');
        }
        if (static::isClassName($name)) {
            return $name;
        }
        return $this->namespacePrefix
            ? $this->namespacePrefix.'\\'.str_replace('.', '\\', $name)
            : str_replace('.', '\\', $name);
    }

    /**
     * Gets DSL name without module
     *
     * @param string|object $name Fully qualified class name or object instance
     * @return string DSL name
     * @throws \InvalidArgumentException If $name is not a string/object
     */
    public function getDslObjectName($name)
    {
        if (is_object($name)) {
            $name = get_class($name);
        }
        elseif(!is_string($name)) {
            throw new \InvalidArgumentException('Invalid type for name, name was not string');
        }
        $names = explode('.', str_replace('\\', '.', $name));
        return array_pop($names);
    }

    /**
     * Gets DSL module name
     *
     * @param string|object $name Fully qualified class name or object instance
     * @return string DSL name
     * @throws \InvalidArgumentException If $name is not a string/object
     */
    public function getDslModuleName($name)
    {
        $names = explode('.', $this->getDslName($name));
        array_pop($names);
        return implode('.', $names);
    }

    private static function isDslName($name)
    {
        return strpos($name, '.') !== false;
    }

    private static function isClassName($name)
    {
        return strpos($name, '\\') !== false;
    }
}
