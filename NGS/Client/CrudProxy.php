<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__ . '/HttpClient.php');
require_once(__DIR__.'/../Patterns/Repository.php');

use NGS\Patterns\AggregateRoot;
use NGS\Patterns\Repository;

/**
 * Proxy service to remote CRUD REST-like API.
 * Single aggregate root instance can be used.
 * New object instance will be returned when doing modifications.
 * All commands are performed on a single aggregate root.
 * Use {@see StandardProxy} when response is not required, or for bulk
 * versions of CRUD commands.
 * It is preferred to use domain patterns instead of this proxy service.
 *
 * @package NGS\Client
 */
class CrudProxy extends BaseProxy
{
    const CRUD_URI = 'Crud.svc';

    /**
     * Create (insert) a single aggregate root on the remote server.
     *  Created object will be returned with its identity
     * and all calculated properties evaluated.
     *
     * @param AggregateRoot $aggregate
     * @return AggregateRoot Persisted aggregate root
     */
    public function create(AggregateRoot $aggregate)
    {
        $class = get_class($aggregate);
        $name = $this->client->getDslName($class);
        $response =
            $this->client->sendRequest(
                self::CRUD_URI.'/'.rawurlencode($name),
                'POST',
                $aggregate->toJson(),
                array(201));
        return $this->client->parseResult($response, $class);
    }

    /**
     * Modify existing aggregate root on the remote server.
     * Aggregate root will be saved and all calculated properties evaluated.
     *
     * @param AggregateRoot $aggregate
     * @return AggregateRoot Persisted aggregate root
     */
    public function update(AggregateRoot $aggregate)
    {
        $class = get_class($aggregate);
        $name = $this->client->getDslName($class);
        $response =
            $this->client->sendRequest(
                self::CRUD_URI.'/'.rawurlencode($name).'?uri='.rawurlencode($aggregate->getURI()),
                'PUT',
                $aggregate->toJson(),
                array(200));
        return $this->client->parseResult($response, $class);
    }

    /**
     * Delete existing aggregate root from the remote server.
     * If possible, aggregate root will be deleted and it's instance
     * will be provided.
     *
     * @param string $class
     * @param string $uri
     * @return AggregateRoot Deleted aggregate root
     */
    public function delete($class, $uri)
    {
        $name = $this->client->getDslName($class);
        $response =
            $this->client->sendRequest(
                self::CRUD_URI.'/'.rawurlencode($name).'?uri='.rawurlencode($uri),
                'DELETE',
                null,
                array(200));
        return $this->client->parseResult($response, $class);
    }

    /**
     * Get domain object from remote server using provided identity.
     * If domain object is not found an exception will be thrown.
     *
     * @param string $class
     * @param string $uri
     * @return AggregateRoot Fetched aggregate root
     */
    public function read($class, $uri)
    {
        $name = $this->client->getDslName($class);
        $response =
            $this->client->sendRequest(
                self::CRUD_URI.'/'.rawurlencode($name).'?uri='.rawurlencode($uri),
                'GET',
                null,
                array(200));
        return $this->client->parseResult($response, $class);
    }
}
