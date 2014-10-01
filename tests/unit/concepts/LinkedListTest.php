<?php
use Struct\LinkedList;
use Struct\UnrolledList;
use NGS\Client\StandardProxy;

class LinkedListTest extends \PHPUnit_Framework_TestCase
{
    private $items;

    public function setUp()
    {
        $proxy = new StandardProxy();
        $proxy->delete(LinkedList::findAll());
    }

    public function tearDown()
    {
        $proxy = new StandardProxy();
        $proxy->delete(LinkedList::findAll());
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
