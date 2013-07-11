<?php

use NGS\Location;
use NGS\Point;
use Test\Address;
use Test\AreaMap;
use Test\Shape;

class LocationPointTest extends PHPUnit_Framework_TestCase
{
    public function testConstructPointFromString()
    {
        $p = new Point('0,0');

        $p = new Point('-10,20');
        $p2 = new Point(' -10,  20');
        $this->assertEquals($p, $p2);
    }

    public function testPropertiesJsonSerialization()
    {
        $item = new Address();
        $item->At = new Location(-10.42, 20);
        $item->P = new Point(-1, 25);
        $expectedJson = '{"URI":null,"ID":0,"Name":"","PostalCode":"","At":{"X":-10.42,"Y":20},"P":"-1,25"}';
        $this->assertSame($expectedJson, $item->toJson());
        return $item;
    }

    public function testPersist()
    {
        $address = new Address();
        $address->At = new Location(-10.42, 20);
        $address->P = new Point(-1, 25);
        $address->persist();
        //$this->assertFalse(true);
        $new = Address::find($address->URI);
        $this->assertEquals($address, $new);
    }

    public function testFromToArray()
    {
        $values = array('X'=>1.234, 'Y'=>5.432);
        $loc = new Location($values);

        $this->assertSame($values, $loc->asArray());
    }
/*
    public function testPersist()
    {
        $item = new Address();
        $item->At = new Location(1.23, 45.55);
        $item->P = new Point(-100, 42);
        $item->persist();

        $newItem = Address::find($item->URI);

        $this->assertEquals($item->At, $newItem->At);

        $item->delete();
    }
*/
    public function testPoint()
    {
        $p1 = new Point(1, 3);
        $p2 = new Point(array('X' => 1, 'Y' => 3));
        $p3 = new Point($p2);
        $p4 = new Point(new Location(1, 3));

        $this->assertEquals($p1, $p2);
        $this->assertEquals($p2, $p3);
        $this->assertEquals($p3, $p4);
    }

    public function testPersistPointCollection()
    {
        $shape = new Shape();

        $points = array(
            new Point(0, 0),
            new Point(1, 1500),
            new Point(-1230, 0),
        );
        $shape->points = $points;
        $shape->persist();

        $fetched = Shape::find($shape->URI);

        $this->assertEquals($points, $fetched->points);
        $this->assertEquals($shape, $fetched);
    }

    public function testPersistLocationCollection()
    {
        $area = new AreaMap();

        $bars = array(
            new Location(0, 0),
            new Location(1.4214, 1500.214),
            new Location(-1230, 0.124141),
        );
        $area->bars = $bars;
        $area->persist();

        $fetched = AreaMap::find($area->URI);

        $this->assertEquals($bars, $fetched->bars);
        $this->assertEquals($area, $fetched);
    }
}
