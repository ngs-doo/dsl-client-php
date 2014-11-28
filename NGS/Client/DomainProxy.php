<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__ . '/HttpClient.php');
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
 * Proxy service to remote REST-like API for basic domain operations
 * such as searching, counting and event sourcing.
 * It is preferred to use domain patterns instead of this proxy service.
 */
class DomainProxy extends BaseProxy
{
    const DOMAIN_URI = 'Domain.svc';

    /**
     * Returns an array of domain objects uniquely represented with their URIs.
     * Only found objects will be returned (array is empty if no objects are found).
     *
     * Example:<br>
     * <code>
     * $proxy = new DomainProxy();
     * $proxy->find('Test\\Item', array('uri1', 'uri2'));
     * </code>
     *
     * @param string $class Domain object class name or instance
     * @param array $uris Array of string URIs
     * @return array Array of found objects or empty array if none found
     */
    public function find($class, array $uris)
    {
        $name = $this->client->getDslName($class);
        $body = json_encode(PrimitiveConverter::toStringArray($uris));
        $response =
            $this->client->sendRequest(
                self::DOMAIN_URI.'/find/'.rawurlencode($name),
                'PUT',
                $body,
                array(200));
        return $this->client->parseResult($response, $class);
    }

    /**
     * Returns an array of all domain objects
     * with up to $limit results.
     *
     * Example:<br>
     * <code>
     * $proxy = new DomainProxy();
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
        $name = $this->client->getDslName($class);
        $lo = QueryString::formatLimitAndOffsetAndOrder($limit, $offset, $order);
        $response =
            $this->client->sendRequest(
                self::DOMAIN_URI.'/search/'.rawurlencode($name).($lo ? '?'.$lo : ''),
                'GET',
                null,
                array(200));
        return $this->client->parseResult($response, $class);
    }

    /**
     * Returns a list of domain objects satisfying {@see NGS\Patterns\Specification}
     * with up to $limit results.
     *
     * @param string        $class
     * @param Specification $specification Specification instance used for searching
     * @param int          $limit
     * @param int          $offset
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
        $objectName = $this->client->getDslName($class);
        $specName = $this->client->getDslModuleName($specification).'+'.$this->client->getDslObjectName($specification);
        $lo = QueryString::formatLimitAndOffsetAndOrder($limit, $offset, $order);
        $response =
            $this->client->sendRequest(
                self::DOMAIN_URI.'/search/'
                    .rawurlencode($objectName)
                    .'?specification='.rawurlencode($specName)
                    .($lo ? '&'.$lo : ''),
                'PUT',
                $specification->toJson(),
                array(200));
        return $this->client->parseResult($response, $this->client->getClassName($objectName));
    }

    /**
     * Returns a list of domain objects satisfying conditions in {@see NGS\Patterns\GenericSearch}
     *
     * @param string|IDomainObject $class
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param array $order
     * @return array
     */
    public function searchGeneric(
        $class,
        array $filters = null,
        $limit = null,
        $offset = null,
        array $order = null)
    {
        $object = $this->client->getDslName($class);
        $lo = QueryString::formatLimitAndOffsetAndOrder($limit, $offset, $order);
        $response =
            $this->client->sendRequest(
                self::DOMAIN_URI.'/search-generic/'.rawurlencode($object).($lo ? '?'.$lo : ''),
                'PUT',
                json_encode($filters),
                array(200));
        return $this->client->parseResult($response, $this->client->getClassName($object));
    }

    /**
     * Count total number of domain objects satisfying conditions in {@see NGS\Patterns\GenericSearch}
     *
     * @param string $class
     * @param array $filters
     * @return int
     */
    public function countGeneric(
        $class,
        array $filters = null)
    {
        $object = $this->client->getDslName($class);
        $response =
            $this->client->sendRequest(
                self::DOMAIN_URI.'/count-generic/'.rawurlencode($object),
                'PUT',
                json_encode($filters),
                array(200));
        return $this->client->parseResult($response, $this->client->getClassName($object));
    }

    /**
     * Returns a total number of domain objects.
     *
     * @param string $class
     * @return integer Total number of objects
     */
    public function count($class)
    {
        $name = $this->client->getDslName($class);
        $response = $this->client->sendRequest(
            self::DOMAIN_URI.'/count/'.rawurlencode($name),
            'GET',
            null,
            array(200));
        $count = $this->client->parseResult($response);
        return PrimitiveConverter::toInteger($count);
    }

    /**
     * Count number of domain objects satisfying {@see NGS\Patterns\Specification}
     *
     * @param Specification $specification
     * @return int Total number of objects
     */
    public function countWithSpecification(Specification $specification)
    {
        $object = $this->client->getDslModuleName($specification);
        $name = $this->client->getDslObjectName($specification);
        $response =
            $this->client->sendRequest(
                self::DOMAIN_URI.'/count/'.rawurlencode($object).'?specification='.rawurlencode($name),
                'PUT',
                $specification->toJson(),
                array(200));
        $count = $this->client->parseResult($response);
        return PrimitiveConverter::toInteger($count);
    }

    /**
     * Send domain event to the server. Server will return identity under which it was stored.
     * Events can't be modified once they are submitted. Only new events can be created.
     *
     * @param DomainEvent $event Event to be executed
     * @param bool $returnInstance Whether to return event instance or event URI
     * @return mixed Event instance or URI string
     */
    public function submitEvent(DomainEvent $event, $returnInstance=false)
    {
        $name = $this->client->getDslName($event);
        $returnResult = $returnInstance ? 'instance' : 'uri';
        $response =
            $this->client->sendRequest(
                self::DOMAIN_URI.'/submit/'.rawurlencode($name).'?result='.$returnResult,
                'POST',
                $event->toJson(),
                array(201));
        if ($returnInstance) {
            return $this->client->parseResult($response, $this->client->getClassName($event));
        }
        $uri = $this->client->parseResult($response);
        return PrimitiveConverter::toString($uri);
    }

    /**
     * Apply domain event to a single aggregate. Server will return modified aggregate root.
     * Events can't be modified once they are submitted. Only new events can be created.
     *
     * @param AggregateDomainEvent $event
     * @param string $uri URI of aggregate root
     * @return \NGS\Patterns\AggregateRoot Aggregate on which event was applied
     */
    public function submitAggregateEvent(AggregateDomainEvent $event, $uri)
    {
        $rootName = $this->client->getDslModuleName($event);
        $eventName = $this->client->getDslObjectName($event);
        $response =
            $this->client->sendRequest(
                self::DOMAIN_URI.'/submit/'.rawurlencode($rootName).'/'.rawurlencode($eventName).'?uri='.rawurlencode($uri),
                'POST',
                $event->toJson(),
                array(201));
        return $this->client->parseResult($response, $this->client->getClassName($rootName));
    }
}
