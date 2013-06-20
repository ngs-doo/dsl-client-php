<?php
use Test\Foo;

class AggregateRootTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException LogicException
     */
    public function testDeleteRootInstanceWithoutPersist()
    {
        $root = new Foo();
        $root->delete();
    }
}
