<?php
use NGS\Client\StandardProxy;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected function deleteAll($class)
    {
        $items = $class::findAll();
        if ($items) {
            $proxy = new StandardProxy();
            $proxy->delete($items);
        }
    }

    public function getFixturesPath()
    {
        return __DIR__.'/fixtures';
    }

    public function getFile($file)
    {
        $path = $this->getFixturesPath().'/'.$file;
        if(!is_file($path)) {
            throw new InvalidArgumentException('TestCase file not found: "'.$path.'"');
        }
        return $path;
    }
}
