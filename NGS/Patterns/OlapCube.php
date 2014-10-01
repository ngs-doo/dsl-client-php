<?php
namespace NGS\Patterns;

require_once(__DIR__ . '/../Client/HttpClient.php');
require_once(__DIR__.'/../Client/StandardProxy.php');
require_once(__DIR__.'/Specification.php');
require_once(__DIR__.'/CubeBuilder.php');

use \NGS\Client\HttpClient;
use \NGS\Client\StandardProxy;
use \NGS\Patterns\Specification;
use \NGS\Patterns\CubeBuilder;

abstract class OlapCube
{
    /**
     * @var \Ngs\Client\HttpClient
     */
    protected $__client__;

    /**
     * @return array Get available dimensions
     */
    public abstract function getDimensions();

    /**
     * @return array Get available facts
     */
    public abstract function getFacts();

    /**
     * Constructs object using target server proxy
     *
     * @param \NGS\Client\HttpClient|null $client Client instance or null
     */
    public function __construct(HttpClient $client = null)
    {
        $this->__client__ = $client;
    }

    public function builder()
    {
        return new CubeBuilder($this);
    }

    /**
     * Populate cube
     *
     * @return \NGS\Patterns\OlapCube Populated cube object
     */
    public function analyze(
        array $dimensions,
        array $facts = array(),
        array $order = array(),
        Specification $specification = null,
        $limit = null,
        $offset = null)
    {
        $proxy = new StandardProxy($this->__client__);
        return $specification === null
            ? $proxy->olapCube($this, $dimensions, $facts, $order, $limit, $offset)
            : $proxy->olapCubeWithSpecification($this, $specification, $dimensions, $facts, $order, $limit, $offset);
    }
}
