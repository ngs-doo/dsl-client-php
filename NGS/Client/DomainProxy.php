<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__.'/../Name.php');
require_once(__DIR__.'/RestHttp.php');
require_once(__DIR__.'/../Converter/PrimitiveConverter.php');
require_once(__DIR__.'/../Patterns/Repository.php');


use NGS\Converter\PrimitiveConverter;
use NGS\Name;
use NGS\Patterns\AggregateDomainEvent;
use NGS\Patterns\DomainEvent;
use NGS\Patterns\IDomainObject;
use NGS\Patterns\Repository;
use NGS\Patterns\Specification;

/**
 * Proxy for executing domain commands, such as searching and counting domain
 * objects and submitting events
 */
class DomainProxy
{
    const DOMAIN_URI = 'Domain.svc';

    /**
     * @var RestHttp Instance of RestHttp client
     */
    protected $http;

    /**
     * @var DomainProxy Singleton instance
     */
    protected static $instance;

    /**
     * Create a new DomainProxy instance
     *
     * @param RestHttp $http RestHttp instance used for http request.
     * Optionally specify an instance, otherwise use a singleton instance
     */
    public function __construct(RestHttp $http = null)
    {
        $this->http = $http !== null ? $http : RestHttp::instance();
    }

    /**
     * Gets singleton instance of Domain.svc proxy
     *
     * @return DomainProxy
     */
    public static function instance()
    {
        if(self::$instance === null)
            self::$instance = new DomainProxy();
        return self::$instance;
    }

    /**
     * Find domain objects by their URIs
     *
     * Example:<br>
     * <code>
     * $proxy = DomainProxy::instance();
     * $proxy->find('Test\\Item', array('uri1', 'uri2'));
     * </code>
     *
     * @param string $class Domain object class name or instance
     * @param array $uris Array of string URIs
     * @return array Array of found objects or empty array if none found
     */
    public function find($class, array $uris)
    {
        $name = Name::full($class);
        $body = array('Name' => $name, 'Uri' => PrimitiveConverter::toStringArray($uris));
        $response =
            $this->http->sendRequest(
                ApplicationProxy::APPLICATION_URI.'/GetDomainObject',
                'POST',
                json_encode($body),
                array(200));
        return RestHttp::parseResult($response, $class);
    }

    /**
     * Search domain objects by type (class)
     *
     * Example:<br>
     * <code>
     * $proxy = DomainProxy::instance();
     * $proxy->search('Test\\Item', 10, 20, array('title' => true));
     * </code>
     *
     * @param string $class Domain object class name or instance
     * @param int $limit Limits maximum number of returned results
     * @param int $offset Offset results by this number
     * @param array $order Array of key=>value pairs: keys are property names,
     * values determine sorting direction: true=ascending, false=descending
     * @return array Array of found objects or empty array if none found
     */
    public function search(
        $class,
        $limit = null,
        $offset = null,
        array $order = null)
    {
        $name = Name::full($class);
        $lo = QueryString::formatLimitAndOffsetAndOrder($limit, $offset, $order);
        $response =
            $this->http->sendRequest(
                self::DOMAIN_URI.'/search/'.rawurlencode($name).($lo ? '?'.$lo : ''),
                'GET',
                null,
                array(200));
        return RestHttp::parseResult($response, $class);
    }

    /**
     * Search domain objects by using specification
     *
     * @param string        $class
     * @param Specification $specification Specification instance used for searching
     * @param type          $limit
     * @param type          $offset
     * @param array         $order
     * @return array Array of found objects or empty array if none found
     */
    public function searchWithSpecification(
        $class,
        Specification $specification,
        $limit = null,
        $offset = null,
        array $order = null)
    {
        $objectName = Name::full($class);
        $specName = Name::parent($specification).'+'.Name::base($specification);
        $lo = QueryString::formatLimitAndOffsetAndOrder($limit, $offset, $order);
        $response =
            $this->http->sendRequest(
                self::DOMAIN_URI.'/search/'
                    .rawurlencode($objectName)
                    .'?specification='.rawurlencode($specName)
                    .($lo ? '&'.$lo : ''),
                'PUT',
                $specification->toJson(),
                array(200));
        return RestHttp::parseResult($response, Name::toClass($class));
    }

    /**
     * Search domain objects by using generic search
     *
     * @param string|IDomainObject $class
     * @param array $filters
     * @param type $limit
     * @param type $offset
     * @param array $order
     * @return type
     */
    public function searchGeneric(
        $class,
        array $filters = null,
        $limit = null,
        $offset = null,
        array $order = null)
    {
        $object = Name::full($class);
        $lo = QueryString::formatLimitAndOffsetAndOrder($limit, $offset, $order);
        $response =
            $this->http->sendRequest(
                self::DOMAIN_URI.'/search-generic/'.rawurlencode($object).($lo ? '?'.$lo : ''),
                'PUT',
                json_encode($filters),
                array(200));
        return RestHttp::parseResult($response, Name::toClass($object));
    }

    /**
     * Count domain objects using generic search
     *
     * @param type $class
     * @param array $filters
     * @return type
     */
    public function countGeneric(
        $class,
        array $filters = null)
    {
        $object = Name::full($class);
        $response =
            $this->http->sendRequest(
                self::DOMAIN_URI.'/count-generic/'.rawurlencode($object),
                'PUT',
                json_encode($filters),
                array(200));
        return RestHttp::parseResult($response, Name::toClass($object));
    }

    /**
     * Count total domain objects of given type (class)
     *
     * @param string $class
     * @return integer Total number of objects
     */
    public function count($class)
    {
        $name = Name::full($class);
        $response = $this->http->sendRequest(
            self::DOMAIN_URI.'/count/'.rawurlencode($name),
            'GET',
            null,
            array(200));
        $count = RestHttp::parseResult($response);
        return PrimitiveConverter::toInteger($count);
    }

    /**
     * Count domain objects using specification
     *
     * @param Specification $specification
     * @return int Total number of objects
     */
    public function countWithSpecification(Specification $specification)
    {
        $object = Name::parent($specification);
        $name = Name::base($specification);
        $response =
            $this->http->sendRequest(
                self::DOMAIN_URI.'/count/'.rawurlencode($object).'?specification='.rawurlencode($name),
                'PUT',
                $specification->toJson(),
                array(200));
        $count = RestHttp::parseResult($response);
        return PrimitiveConverter::toInteger($count);
    }

    /**
     * Submit event
     *
     * @param DomainEvent $event Event to be executed
     * @return string Event URI
     */
    public function submitEvent(DomainEvent $event)
    {
        $name = Name::full($event);
        $response =
            $this->http->sendRequest(
                self::DOMAIN_URI.'/submit/'.rawurlencode($name),
                'POST',
                $event->toJson(),
                array(201));
        $uri = RestHttp::parseResult($response);
        return PrimitiveConverter::toString($uri);
    }

    /**
     * Apply aggregate event on a single aggregate root
     *
     * @param AggregateDomainEvent $event
     * @param string $uri URI of aggregate root
     * @return \NGS\Patterns\AggregateRoot Aggregate on which event was applied
     */
    public function submitAggregateEvent(AggregateDomainEvent $event, $uri)
    {
        $object = Name::parent($event);
        $name = Name::base($event);
        $response =
            $this->http->sendRequest(
                self::DOMAIN_URI.'/submit/'.rawurlencode($object).'/'.rawurlencode($name).'?uri='.rawurlencode($uri),
                'POST',
                $event->toJson(),
                array(201));
        Repository::instance()->invalidate($object, $uri);
        return RestHttp::parseResult($response, Name::toClass($object));
    }

}
