<?php

use NGS\Client\StandardProxy;
use Test\Foo;
use Test\Bar;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    public function testDeleteArrayOfAggregates()
    {
        $proxy = new StandardProxy();

        $items = Foo::findAll();

        $proxy->delete($items);

        $a = new Foo(array('bar'=>'a'));
        $b = new Foo(array('bar'=>'b'));
        $items = array($a, $b);
        $uris = $proxy->insert($items);

        $proxy->delete(Foo::find($uris));

        $items = Foo::findAll();

        $this->assertEmpty($items);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDeleteArrayOfInvalidTypes()
    {
        $items = array(
            new Foo(array('bar'=>'a')),
            new Bar()
        );
        $proxy = new StandardProxy();
        $proxy->delete($items);
    }
}
