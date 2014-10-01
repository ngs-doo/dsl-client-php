<?php
namespace NGS\Patterns;

require_once(__DIR__.'/../Converter/PrimitiveConverter.php');
require_once(__DIR__.'/../Client/DomainProxy.php');

use \NGS\Client\DomainProxy;
use NGS\Client\HttpClient;
use NGS\Client\StandardProxy;
use \NGS\Converter\PrimitiveConverter;
use NGS\Name;

class SearchBuilder extends Search
{
    private $specification;
    private $client;

    public function __construct(Specification $specification, HttpClient $client = null)
    {
        $this->specification = $specification;
        $this->client = $client;
    }

    public function __get($name)
    {
        return $this->specification->{$name};
    }

    public function __set($name, $value)
    {
        $this->specification->{$name} = $value;
    }

    public function search()
    {
        $class = get_class($this->specification);
        $target = substr($class, 0, strrpos($class, '\\'));
        $proxy = new DomainProxy($this->client);
        return $proxy->searchWithSpecification(
                $target,
                $this->specification,
                $this->limit,
                $this->offset,
                $this->order);
    }
}
