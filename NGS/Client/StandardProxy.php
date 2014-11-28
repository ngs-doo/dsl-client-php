<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__ . '/HttpClient.php');
require_once(__DIR__.'/../Patterns/Repository.php');
require_once(__DIR__.'/QueryString.php');

use InvalidArgumentException;
use NGS\Converter\ObjectConverter;
use NGS\Name;
use NGS\Patterns\Repository;
use NGS\Utils;

/**
 * Proxy service to various domain operations such as bulk persistence,
 * data analysis, and remote service calls counting and event sourcing.
 * It is preferred to use domain patterns instead of this proxy service.
 */
class StandardProxy extends BaseProxy
{
    const STANDARD_URI = 'Commands.svc';
    const APPLICATION_URI  = 'RestApplication.svc';

    /**
     * Insert multiple aggregates with single request to the remote server
     *
     * @param array $aggregates Array of \NGS\Patterns\AggregateRoot instances
     * @return array|mixed
     */
    public function insert(array $aggregates)
    {
        if(empty($aggregates))
            return array();
        $response = $this->persist('POST', $aggregates);
        return $this->client->parseResult($response);
    }

    private static function invalidate(array $aggregates)
    {
        $uris = array();
        foreach($aggregates as $root) {
            $uris[] = $root->URI;
        }
    }

    /**
     * Update multiple aggregates with single command/request
     *
     * @param array $aggregates Array of \NGS\Patterns\AggregateRoot instances
     */
    public function update(array $aggregates)
    {
        if(!empty($aggregates)) {
            $this->persist('PUT', $aggregates);
            self::invalidate($aggregates);
        }
    }

    /**
     * Delete multiple aggregates with single command/request
     *
     * @param array $aggregates
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function delete(array $aggregates)
    {
        if(empty($aggregates))
            return null;
        if(!is_object($aggregates[0]))
            throw new InvalidArgumentException("Could not delete aggregates. First element was not an object.");
        $class = get_class($aggregates[0]);
        foreach($aggregates as $index => $item) {
            if (!$item instanceof $class) {
                throw new InvalidArgumentException('Could not delete aggregates. Element with index "'.$index.'" was not an instance of "'.$class.'", type was "'.Utils::getType($item).'"');
            }
            if ($item->URI === null) {
                throw new InvalidArgumentException('Could not delete aggregate element "'.$class.'" with index "'.$index.'". Aggregate URI was null');
            }
        }
        $converter = ObjectConverter::getConverter($class, ObjectConverter::JSON_TYPE);
        $body = array(
            'RootName' => $this->client->getDslName($class),
            // 'ToDelete' is json encoded inside json
            'ToDelete' => $converter::toJson($aggregates)
        );
        $body = json_encode($body);

        $result = $this->client->sendRequest(
            self::APPLICATION_URI.'/PersistAggregateRoot',
            'POST',
            $body,
            array(200, 201));
        self::invalidate($aggregates);
        return $result;
    }

    private function persist($method, array $aggregates)
    {
        $class = get_class($aggregates[0]);
        $name = $this->client->getDslName($class);
        $values = array_map(function($it) { return $it->toArray(); }, $aggregates);
        return
            $this->client->sendRequest(
                self::STANDARD_URI.'/persist/'.rawurlencode($name),
                $method,
                json_encode($values),
                array(200));
    }

    /**
     * Perform OLAP analysis on a cube using specification
     *
     * @return array Results
     */
    public function olapCubeWithSpecification(
        $cube,
        $specification,
        array $dimensions,
        array $facts,
        array $order = array(),
        $limit = null,
        $offset = null)
    {
        $cube = $this->client->getDslName($cube);
        $name = $this->client->getDslObjectName($specification);
        $fullName = $this->client->getDslName($specification);
        if(strncmp($fullName, $cube, strlen($cube)) != 0)
            $name = substr($fullName, 0, strlen($fullName) - strlen($name) - 1).'+'.$name;
        $arguments = QueryString::prepareCubeCall($dimensions, $facts, $order, $limit, $offset);
        $response =
            $this->client->sendRequest(
                self::STANDARD_URI.'/olap/'.rawurlencode($cube).'?specification='.rawurlencode($name).'&'.$arguments,
                'PUT',
                $specification->toJson(),
                array(201));
        return $this->client->parseResult($response);
    }

    /**
     * Performs OLAP analysis on a cube
     */
    public function olapCube(
        $cube,
        array $dimensions,
        array $facts,
        array $order = array(),
        $limit = null,
        $offset = null)
    {
        $cube = $this->client->getDslName($cube);
        $arguments = QueryString::prepareCubeCall($dimensions, $facts, $order, $limit, $offset);
        $response =
            $this->client->sendRequest(
                self::STANDARD_URI.'/olap/'.rawurlencode($cube).'?'.$arguments,
                'GET',
                null,
                array(201));
        return $this->client->parseResult($response);
    }

    /**
     * Execute custom server service
     *
     * @return mixed
     */
    public function execute(
        $service,
        $body=null
    )
    {
        if(is_array($body))
            $body = json_encode($body);
        if(!is_string($body) && $body!==null)
            throw new InvalidArgumentException("Execute body must be array or string");

        $response =
            $this->client->sendRequest(
                self::STANDARD_URI.'/execute/'.rawurlencode($service),
                'POST',
                $body,
                array(200, 201));
        return $this->client->parseResult($response);
    }
}
