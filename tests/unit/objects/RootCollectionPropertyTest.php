<?php

class RootCollectionPropertyTest extends PHPUnit_Framework_TestCase
{
    private $roots;

    public function setUp()
    {
        $r1 = new \Properties\rootObject(array('title'=>'a'));
        $r1->persist();
        $r2 = new \Properties\rootObject(array('title'=>'b'));
        $r2->persist();
        $this->roots = array($r1, $r2);
    }

    public function tearDown()
    {
        $proxy = new \NGS\Client\StandardProxy();
        $proxy->delete(\Properties\rootCollectionRoot::findAll());
        $proxy->delete(\Properties\rootObject::findAll());
    }

    public function testPersistAndReadRootCollectionProperty()
    {
        $roots = $this->roots;
        $uris = array_map(function($r) { return $r->URI; }, $roots);

        $obj = new \Properties\rootCollectionRoot();
        $obj->items = $roots;
        $obj->itemsNull = $roots;
        $obj->itemsSnap = $roots;
        $obj->persist();

        $loaded = \Properties\rootCollectionRoot::find($obj->URI);

        $this->assertEquals($uris, $obj->itemsURI);
        $this->assertEquals($uris, $obj->itemsNullURI);
        $this->assertEquals($uris, $obj->itemsSnapURI);
        $this->assertEquals($uris, $loaded->itemsURI);
        $this->assertEquals($uris, $loaded->itemsNullURI);
        $this->assertEquals($uris, $loaded->itemsSnapURI);
        $this->assertEquals($roots, $obj->items);
        $this->assertEquals($roots, $obj->itemsNull);
        $this->assertEquals($roots, $obj->itemsSnap);
        $this->assertEquals($roots, $loaded->items);
        $this->assertEquals($roots, $loaded->itemsNull);
        $this->assertEquals($roots, $loaded->itemsSnap);
    }

    public function testPropertyIsset()
    {
        $obj = new \Properties\rootCollectionRoot();

        $this->assertFalse(isset($obj->items));
        $this->assertFalse(isset($obj->itemsURI));

        $obj->items = $this->roots;

        $this->assertEquals($this->roots, $obj->items);

        $this->assertTrue(isset($obj->items));
        $this->assertTrue(isset($obj->itemsURI));

        $itemsURIs = array_map(function($r) { return $r->URI; }, $this->roots);

        $this->assertSame($itemsURIs, $obj->itemsURI);
    }

    public function testAddRemoveMethods()
    {
        $roots = $this->roots;
        $rootUris = array($roots[0]->URI, $roots[1]->URI);

        $obj = new \Properties\rootCollectionRoot();
        $this->assertNull($obj->items);

        $obj->additems($roots[0]);
        $this->assertEquals(array($roots[0]), $obj->items);
        $this->assertEquals(array($roots[0]->URI), $obj->itemsURI);

        $obj->removeItems($roots[0]);
        $this->assertEmpty($obj->items);

        $obj->addItems($roots[0]);
        $obj->addItems($roots[1]);
        $this->assertEquals($roots, $obj->items);
        $this->assertEquals($rootUris, $obj->itemsURI);

        $obj->removeItems($roots[0]);
        $this->assertSame(array($roots[1]->URI), $obj->itemsURI);
        $this->assertEquals(array($roots[1]), $obj->items);

        $obj->removeItems($roots[1]);
        $this->assertEmpty($obj->items);
        $this->assertEmpty($obj->itemsURI);

        $obj->items = $roots;
        $other = new \Properties\rootCollectionRoot();
        $other->addItems($roots[0]);
        $other->addItems($roots[1]);
        $this->assertEquals($obj->items, $other->items);
        $this->assertEquals($obj->itemsURI, $other->itemsURI);

        $obj->removeItems($roots[1]->URI);
        $this->assertEquals(array($roots[0]->URI), $obj->itemsURI);
        $this->assertEquals(array($roots[0]), $obj->items);
    }

    public function testUnsetMethod()
    {
        $roots = $this->roots;
        $obj = new \Properties\rootCollectionRoot();

        $obj->items = $roots;
        $this->assertEquals($roots, $obj->items);
        $this->assertEquals(array($roots[0]->URI, $roots[1]->URI), $obj->itemsURI);

        unset($obj->items);
        $this->assertNull($obj->items);
        $this->assertNull($obj->itemsURI);
    }

    public function testConverters()
    {
        $roots = $this->roots;
        $rootUris = array($roots[0]->URI, $roots[1]->URI);

        $obj = new \Properties\rootCollectionRoot();
        $obj->additems($roots[0]);

        $clone = clone $obj;
        $this->assertEquals($clone, $obj);

        // not equal: lazy loaded items property is not assigned in constructor
        //$this->assertEquals($obj, \Properties\rootCollectionRootArrayConverter::fromArray($obj->toArray()));
        //$this->assertEquals($clone, \Properties\rootCollectionRootArrayConverter::fromArray(\Properties\rootCollectionRootArrayConverter::toArray($obj)));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssignNonPersistedAggregate()
    {
        $obj = new \Properties\rootCollectionRoot();

        $nonPersistedRoot = new \Properties\RootObject(array('title'=>'a'));

        $obj->items = array($nonPersistedRoot);
    }
}
