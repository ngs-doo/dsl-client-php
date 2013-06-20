<?php
use Store\ProductList;
use Store\Product;
use Struct\LinkedList;
use Struct\UnrolledList;

class SnowflakeTest extends \BaseTestCase
{
    public function setUp()
    {
        $this->deleteAll('Store\Product');
        $this->deleteAll('Struct\LinkedList');
    }

    public function testOrderBy()
    {
        $items = array(
            new Product(array('Name'=>'second', 'Price'=>'100')),
            new Product(array('Name'=>'first', 'Price'=>'200')),
            new Product(array('Name'=>'third', 'Price'=>'100'))
        );

        foreach($items as $it)
            $it->persist();

        // order by Price desc, Name asc;
        $sorted = ProductList::findAll();
        $this->assertEquals($items[0]->URI, $sorted[1]->URI);
        $this->assertEquals($items[1]->URI, $sorted[0]->URI);
        $this->assertEquals($items[2]->URI, $sorted[2]->URI);
    }

    public function testPathNavigation()
    {
        $a = new LinkedList(array('Number'=>1));
        $b = new LinkedList(array('Number'=>2));
        $c = new LinkedList(array('Number'=>3));

        // create circular list
        $c->persist();
        $b->Next = $c;
        $b->persist();
        $a->Next = $b;
        $a->persist();
        $c->Next = $a;
        $c->persist();

        $a = LinkedList::find('1');
        $this->assertEquals($a->URI, $a->Next->Next->Next->URI);

        $unrolled = UnrolledList::findByNumber(1);
        $item = $unrolled[0];
        $this->assertSame(1, $item->Number);
        $this->assertSame(2, $item->FirstNumber);
        $this->assertSame(3, $item->SecondNumber);
    }
}
