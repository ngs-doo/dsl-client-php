<?php
namespace NGS\Client;

require_once(__DIR__.'/../Utils.php');
require_once(__DIR__ . '/HttpClient.php');
require_once(__DIR__.'/QueryString.php');

use NGS\Converter\PrimitiveConverter;
use NGS\Converter\ObjectConverter;
use NGS\Patterns\OlapCube;
use NGS\Patterns\Snapshot;
use NGS\Patterns\Specification;
use NGS\Patterns\GenericSearch;

/**
 * Proxy service to reporting operations such as document generation,
 * report population and history lookup.
 * Report should be used to minimize calls to server.
 */
class ReportingProxy extends BaseProxy
{
    const REPORTING_URI = 'Reporting.svc';

    /**
     * Populate report. Send message to server with serialized report specification.
     * @todo: API change, Report/Result
     *
     * @param $report
     * @return mixed
     */
    public function populateReport($report)
    {
        $class = get_class($report);
        $name = $this->client->getDslName($report);
        $response =
        $this->client->sendRequest(
            self::REPORTING_URI.'/report/'.rawurlencode($name),
            'PUT',
            $report->toJson(),
            array(200));
        return $this->client->parseResult($response, $class);
    }

    /**
     * Create document from report. Send message to server with serialized report specification.
     * Server will return template populated with found data.
     *
     * @param  mixed  $report    Report instance
     * @param  string $templater Templater name
     * @return string            Report contents
     */
    public function createReport($report, $templater)
    {
        $name = $this->client->getDslName($report);
        return
            $this->client->sendRequest(
                self::REPORTING_URI.'/report/'.rawurlencode($name).'/'.rawurlencode($templater),
                'PUT',
                $report->toJson(),
                array(201),
                'application/octet-stream');
    }

    /**
     * Perform data analysis on specified data source.
     * Data source is filtered using provided specification.
     * Analysis is performed by grouping data by dimensions
     * and aggregating information using specified facts.
     *
     * @param  \NGS\Patterns\OlapCube       $cube
     * @param  \NGS\Patterns\Specification  $specification
     * @param  string                       $templater
     * @param  array                        $dimensions
     * @param  array                        $facts
     * @param  array                        $order
     * @param  int                          $limit
     * @param  int                          $offset
     * @return string Report contents
     */
    public function olapCubeWithSpecification(
        OlapCube $cube,
        Specification $specification,
        $templater,
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
        return
            $this->client->sendRequest(
                self::REPORTING_URI.'/olap/'.rawurlencode($cube).'/'.rawurlencode($templater).'?specification='.rawurlencode($name).'&'.$arguments,
                'PUT',
                $specification->toJson(),
                array(201),
                'application/octet-stream');
    }

    /**
     * Perform data analysis on specified data source.
     * Analysis is performed by grouping data by dimensions
     * and aggregating information using specified facts.
     *
     * @param \NGS\Patterns\OlapCube        $cube
     * @param  string                       $templater
     * @param  array                        $dimensions
     * @param  array                        $facts
     * @param  array                        $order
     * @param  int                          $limit
     * @param  int                          $offset
     * @return string Report contents
     */
    public function olapCube(
        $cube,
        $templater,
        array $dimensions,
        array $facts,
        array $order = array(),
        $limit = null,
        $offset = null)
    {
        $cube = $this->client->getDslName($cube);
        $arguments = QueryString::prepareCubeCall($dimensions, $facts, $order, $limit, $offset);

        return
            $this->client->sendRequest(
                self::REPORTING_URI.'/olap/'.rawurlencode($cube).'/'.rawurlencode($templater).'?'.$arguments,
                'GET',
                null,
                array(201),
                'application/octet-stream');
    }

    /**
     * Get aggregate root history.
     * History is collection of snapshots made at state changes.
     *
     * @param  string $class Object class name
     * @param  string $uri   Object URI
     * @return array         List of history entries
     */
    public function getHistory($class, $uri)
    {
        return is_array($uri)
            ? $this->getMultipleHistory($class, $uri)
            : $this->getSingleHistory($class, $uri);
    }

    private static function parseHistoryResponse($response, $class)
    {
        $data = json_decode($response, true);
        $result = array();
        $converter = ObjectConverter::getConverter($class, ObjectConverter::ARRAY_TYPE);

        foreach($data as $dataItem)
        {
            $history = array();
            $snapshots = $dataItem['Snapshots'];
            foreach($snapshots as $snapshotItem)
            {
                $history[] =
                    new Snapshot(
                        $snapshotItem['At'],
                        $snapshotItem['Action'],
                        $converter::fromArray($snapshotItem['Value']));
            }
            $result[] = $history;
        }

        return $result;
    }

    private function getMultipleHistory($class, $uris)
    {
        $name = $this->client->getDslName($class);
        $body = json_encode(PrimitiveConverter::toStringArray($uris));
        $response =
            $this->client->sendRequest(
                self::REPORTING_URI.'/history/'.rawurlencode($name),
                'PUT',
                $body,
                array(200));
        return self::parseHistoryResponse($response, $class);
    }

    private function getSingleHistory($class, $uri)
    {
        $name = $this->client->getDslName($class);
        $response =
            $this->client->sendRequest(
                self::REPORTING_URI.'/history/'.rawurlencode($name).'/'.rawurlencode($uri),
                'GET',
                null,
                array(200));
        $history = self::parseHistoryResponse($response, $class);
        return isset($history[0]) ? $history[0] : array();
    }

    /**
     * Populate template using found domain object.
     * Optionally convert document to pdf.
     *
     * @param  string $file  Template file to populate
     * @param  string $class Object class
     * @param  string $uri   Object URI
     * @return string        Populated template contents
     */
    public function findTemplater(
        $file,
        $class,
        $uri=null)
    {
        $name = $this->client->getDslName($class);
        $uriQuery = $uri!==null ? '/'.$uri : '';
        return
            $this->client->sendRequest(
                self::REPORTING_URI.'/templater/'.rawurlencode($file).'/'.rawurlencode($name).$uriQuery,
                'GET',
                null,
                array(200),
                'application/octet-stream');
    }

    /**
     * Populate template using domain objects which satisfy
     * {@ses NGS\Patterns\Specification}.
     * Optionally convert document to pdf.
     *
     * @param  string $file  Template file to populate
     * @param  \NGS\Patterns\Specification Specification to be searched
     * @return string        Populated template contents
     */
    public function searchTemplater(
        $file,
        Specification $specification)
    {
        $object = $this->client->getDslModuleName($specification);
        $name = $this->client->getDslObjectName($specification);
        return
            $this->client->sendRequest(
                self::REPORTING_URI.'/templater/'.rawurlencode($file).'/'.rawurlencode($object).'?specification='.rawurlencode($name),
                'PUT',
                $specification->toJson(),
                array(200),
                'application/octet-stream');
    }

    /**
     * Populate template using domain objects which satisfy
     * {@ses NGS\Patterns\GenericSearch}.
     *
     * @param  string $file  Template file to populate
     * @param  \NGS\Patterns\GenericSearch $search
     * @return string        Populated template contents
     */
    public function searchTemplaterGeneric(
        $file,
        GenericSearch $search)
    {
        $object = $this->client->getDslName($search->getObject());
        return
            $this->client->sendRequest(
                self::REPORTING_URI.'/templater-generic/'.rawurlencode($file).'/'.rawurlencode($object),
                'PUT',
                json_encode($search->getFilters()),
                array(200),
                'application/octet-stream');
    }
}
