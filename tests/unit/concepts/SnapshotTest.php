<?php
use Shop\Product;
use Shop\Customer;
use Shop\Order;

class SnapshotTest extends \BaseTestCase
{
    public function testPersistSnapshot()
    {
        $john = new Customer(array('name' => 'John Doe'));
        $john->persist();

        $order = new Order();
        $order->customer = $john;
        $order->persist();

        $johnDoe = clone $john;
        $john->name = 'John Smith';
        $john->persist();

        $fetched = Order::find($order->URI);
        $this->assertEquals($johnDoe, $fetched->customer);
    }

    public function testPersistSnapshotCollection()
    {
        $crowbar = new Product(array('name' => 'Crowbar',    'price' => 20));
        $battery = new Product(array('name' => 'Light bulb', 'price' => 1.50));
        $crowbar->persist();
        $battery->persist();

        $johnDoe = new Customer(array('name' => 'John Doe'));
        $johnDoe->persist();

        $order = new Order();
        $order->customer = $johnDoe;
        $order->products = array($crowbar, $battery);
        $order->persist();

        $crowbarOld = clone $crowbar;
        $batteryOld = clone $battery;
        $crowbar->price = 1000;
        $battery->price = 150;
        $crowbar->persist();
        $battery->persist();

        $order = Order::find($order->URI);

        $this->assertEquals(array($crowbarOld, $batteryOld), $order->products);
    }
}