<?php
use NGS\Client\DomainProxy;
use NGS\Client\StandardProxy;
use Test\Foo;

class DomainProxyTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        StandardProxy::instance()->delete(Foo::findAll());
        $this->items = array(
            new Foo(array('bar' => 'a',  'num' => 1)),
            new Foo(array('bar' => 'ab', 'num' => 2)),
        );
        StandardProxy::instance()->insert($this->items);
    }

    protected function tearDown()
    {
        StandardProxy::instance()->delete(Foo::findAll());
    }

    public function testFindByUrisWithStringKeys()
    {
        $uris = array('key1'=>'a', 'key2'=>'ab');

        $items = Foo::find($uris);

        $this->assertSame(2, count($items));
    }
}
