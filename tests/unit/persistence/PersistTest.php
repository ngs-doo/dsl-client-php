<?php
use Test\ItemHolder;
use Test\Item;
use Test\EntHolder;
use NGS\Client\StandardProxy;

/*
    root Item (bar)
    {
        string bar;
        int num;
    }
    entity EntCompKey
    {
        string name;
        int code;
    }

    root ItemHolder
    {
        Item *item;
        Item? *optItem;
    }

    root EntHolder
    {
        EntCompKey ent;
        EntCompKey? optEnt;
    }
*/

class PersistTest extends \PHPUnit_Framework_TestCase
{
    private $items;

    public function setUp()
    {
        StandardProxy::instance()->delete(ItemHolder::findAll());
        StandardProxy::instance()->delete(Item::findAll());
    }

    public function tearDown()
    {
        StandardProxy::instance()->delete(ItemHolder::findAll());
        StandardProxy::instance()->delete(Item::findAll());
    }

    /**
     * @expectedException LogicException
     */
    public function testNonNullableRootPropertyIsNull()
    {
        $root = new ItemHolder();
        // $root->item is null by default
        $root->persist();
    }

    public function testNullableRootPropertyIsNotNull()
    {
        $root = new ItemHolder();
        $item = new Item();
        $item->persist();
        $root->item = $item;
        $root->persist();
    }
}
