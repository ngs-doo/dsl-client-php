<?php
use Struct\LinkedList;

class ReferenceTest extends \BaseTestCase
{
    public function setUp()
    {
        $this->deleteAll('Struct\LinkedList');
    }

    public function testList()
    {
        $a = new LinkedList(array('Number'=>1));
        $b = new LinkedList(array('Number'=>2));
        $c = new LinkedList(array('Number'=>3));

        $c->persist();
        $b->Next = $c;
        $b->persist();
        $a->Next = $b;
        $a->persist();

        $this->assertEquals($b, $a->Next);
        $this->assertEquals($c, $a->Next->Next);
    }
}
