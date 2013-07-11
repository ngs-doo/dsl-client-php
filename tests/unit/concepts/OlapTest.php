<?php
use NGS\Client\StandardProxy;
use Test\Foo;
use Test\FooGrid;
use Test\FooCube;

class OlapTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $items = array(
            new Foo(array('bar'=>'aa', 'num'=>1)),
            new Foo(array('bar'=>'ab', 'num'=>5)),
            new Foo(array('bar'=>'c', 'num'=>0)),
        );
        $proxy = new StandardProxy();
        $proxy->insert($items);
    }

    public function tearDown()
    {
        StandardProxy::instance()->delete(Foo::findAll());
    }

    public function testCube()
    {
        $proxy = new StandardProxy();
        $dimensions = array();
        $facts = array('count', 'total', 'average');
        $olapData = $proxy->olapCube('Test.FooCube', $dimensions, $facts);

        $this->assertSame(3, $olapData[0]['count']);
        $this->assertSame(6, $olapData[0]['total']);
        $this->assertSame((float)2, $olapData[0]['average']);
    }

    public function testCubeWithSpecification()
    {
        $dimensions = array();
        $facts = array('count', 'total', 'average');
        $spec = new \Test\FooCube\findByBar(array('query'=>'a'));

        $cube = new FooCube();
        $olapData = $cube->analyze($dimensions, $facts, array(), $spec);

        $this->assertSame(2, $olapData[0]['count']);
        $this->assertSame(6, $olapData[0]['total']);
        $this->assertSame((float)3.0, $olapData[0]['average']);
    }

    public function testOrderArguments()
    {
        $cube = new FooCube();
        $dims = $cube->getDimensions();
        $facts = $cube->getFacts();

        $order = array('num'=>true);
        $items1 = $cube->analyze($dims, $facts, $order);

        $this->assertSame(0, $items1[0]['num']);
        $this->assertSame(1, $items1[1]['num']);
        $this->assertSame(5, $items1[2]['num']);

        $order = array('num'); // same as array('num'=>true)
        $items2 = $cube->analyze($dims, $facts, $order);

        $this->assertSame($items1, $items2);

        $order = array('num'=>false);
        $items3 = $cube->analyze($dims, $facts, $order);

        $this->assertSame(array_reverse($items1), $items3);

        $order = array('bar', 'num'=>false);
        $items4 = $cube->analyze($dims, $facts, $order);

        $this->assertSame(1, $items4[0]['num']);
        $this->assertSame(5, $items4[1]['num']);
        $this->assertSame(0, $items4[2]['num']);

        $order = array('bar'=>false, 'num'=>true);
        $items5 = $cube->analyze($dims, $facts, $order);

        $this->assertSame(array_reverse($items4), $items5);
    }
}
