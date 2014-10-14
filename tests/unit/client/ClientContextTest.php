<?php

use NGS\Client\HttpClient;
use Shop\Order;
use Store\Group;

class ClientContextTest extends BaseTestCase
{
    protected $staticClient;
    protected $badClient;

    public function setUp()
    {
        $this->staticClient = HttpClient::instance();
        // use client which will break
        $this->badClient = new FaultyClient('http://localhost:47543/nobody-here');
    }

    public function tearDown()
    {
        // revert global static client for other tests
        HttpClient::instance($this->staticClient);
    }

    public function testReferenceHasContext()
    {
        $newCustomer = new Shop\Customer();
        $newCustomer->create();
        $newOrder = new Order();
        $newOrder->customer = $newCustomer;
        $newOrder = $newOrder->create();

        $order = Order::find($newOrder->URI);

        HttpClient::instance($this->badClient);

        $customer = $order->getCustomer();

        $order->delete();
        $customer->delete();
    }

    public function testDetailLazyLoadHasContext()
    {
        $newGroup = new Group();
        $newGroup->create();
        $product = new \Store\Product();
        $product->Group = $newGroup;
        $product->create();

        $group = Group::find($newGroup->URI);

        HttpClient::instance($this->badClient);

        // lazy load
        $this->assertEquals($product, $group->Products[0]);
    }

    public function testCloneCopiesContext()
    {
        $client = new FaultyClient('http://bad-host');
        $group = new Group(array(), 'build_internal', $client);
        $cloned = clone $group;

        $ref = new ReflectionClass($cloned);
        $clientProperty = $ref->getProperty('__client__');
        $clientProperty->setAccessible(true);
        $clientCloned = $clientProperty->getValue($cloned);
        $this->assertSame($client, $clientCloned);
    }
}
