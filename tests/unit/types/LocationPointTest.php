<?php

use NGS\Location;
use NGS\Point;
use Test\Address;

class LocationPointTest extends PHPUnit_Framework_TestCase
{
    public function testFromToArray()
    {
        $values = array('X'=>1.234, 'Y'=>5.432);
        $loc = new Location($values);

        $this->assertSame($values, $loc->toArray());
    }

    public function testPersist()
    {
        $item = new Address();
        $item->At = new Location(1.23, 45.55);

        $item->persist();

        $newItem = Address::find($item->URI);

        $this->assertEquals($item->At, $newItem->At);

        $item->delete();
    }

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
}
