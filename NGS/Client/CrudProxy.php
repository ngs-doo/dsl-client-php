<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__.'/../Name.php');
require_once(__DIR__.'/RestHttp.php');
require_once(__DIR__.'/../Patterns/Repository.php');

use NGS\Name;
use NGS\Patterns\AggregateRoot;
use NGS\Patterns\Repository;
use NGS\Utils;

/**
 * Proxy used for executing CRUD commands.
 * All commands are performed on a single aggregate root.
 * Use StandardProxy for bulk versions  of create/update/delete.
 *
 * @package NGS\Client
 */
class CrudProxy
{
    const CRUD_URI = 'Crud.svc';

    protected $http;

    protected static $instance;

    /**
     * Create a new CrudProxy instance
     *
     * @param RestHttp $http RestHttp instance used for http request.
     * Optionally specify an instance, otherwise use a singleton instance
     */
    public function __construct(RestHttp $http = null)
    {
        $this->http = $http !== null ? $http : RestHttp::instance();
    }

    /**
     * Gets singleton instance of Crud.svc proxy
     *
     * @return CrudProxy
     */
    public static function instance()
    {
        if(self::$instance === null)
            self::$instance = new CrudProxy();
        return self::$instance;
    }

    /**
     * Create (insert) a single aggregate root
     *
     * @param AggregateRoot $aggregate
     * @return AggregateRoot Persisted aggregate root
     */
    public function create(AggregateRoot $aggregate)
    {
        $class = get_class($aggregate);
        $name = Name::full($class);
        $response =
            $this->http->sendRequest(
                self::CRUD_URI.'/'.rawurlencode($name),
                'POST',
                $aggregate->toJson(),
                array(201));
        return RestHttp::parseResult($response, $class);
    }

    /**
     * Update a single aggregate root
     *
     * @param AggregateRoot $aggregate
     * @return AggregateRoot Persisted aggregate root
     */
    public function update(AggregateRoot $aggregate)
    {
        $class = get_class($aggregate);
        $name = Name::full($class);
        $response =
            $this->http->sendRequest(
                self::CRUD_URI.'/'.rawurlencode($name).'?uri='.rawurlencode($aggregate->getURI()),
                'PUT',
                $aggregate->toJson(),
                array(200));
        Repository::instance()->invalidate($class, $aggregate->URI);
        return RestHttp::parseResult($response, $class);
    }

    /**
     * Delete a single aggregate root using URI as identifier
     *
     * @param string $class
     * @param string $uri
     * @return AggregateRoot Deleted aggregate root
     */
    public function delete($class, $uri)
    {
        $name = Name::full($class);
        $response =
            $this->http->sendRequest(
                self::CRUD_URI.'/'.rawurlencode($name).'?uri='.rawurlencode($uri),
                'DELETE',
                null,
                array(200));
        Repository::instance()->invalidate($class, $uri);
        return RestHttp::parseResult($response, $class);
    }

    /**
     * Read (fetch) a single aggregate root using URI as identifier
     *
     * @param string $class
     * @param string $uri
     * @return AggregateRoot Fetched aggregate root
     */
    public function read($class, $uri)
    {
        $name = Name::full($class);
        $response =
            $this->http->sendRequest(
                self::CRUD_URI.'/'.rawurlencode($name).'?uri='.rawurlencode($uri),
                'GET',
                null,
                array(200));
        return RestHttp::parseResult($response, $class);
    }
}
