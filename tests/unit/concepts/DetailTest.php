<?php
use Store\ProductList;
use Store\Product;
use Store\Group;
use Store\Package;

class DetailTest extends \BaseTestCase
{
    public function setUp()
    {
        $this->deleteAll('Store\Product');
        $this->deleteAll('Store\Group');
        $this->deleteAll('Store\Package');
    }
    
    public function testDetail()
    {
        $group = new Group(array('Name'=>'test'));
        $group->persist();

        $prod = new Product(array(
            'Name'  =>'second',
            'Price' =>'100',
            'Group' => $group
        ));
        $prod->persist();

        $item = Group::find($group->URI);

        $this->assertEquals($prod->URI, $item->Products[0]->URI);

        $this->assertNull($item->Products[0]->Packages);

        $package = new \Store\Package();
        $package->Product = $prod; //$item->Products[0];
        $package->persist();

        $foundProd = Product::find($prod->URI);
        $this->assertEquals(array($package), $foundProd->Packages);
        
        $itemReloaded = Group::find($group->URI);

        $this->assertEquals($package, $itemReloaded->Products[0]->Packages[0]);
    }

    /**
     * @expectedException \LogicException
     */
    public function testReadOnly()
    {
        $group = new Group(array('Name'=>'test'));
        $group->Products = null;
    }

    public function testDetailInSnowflakeSpecification()
    {
        $prod1 = new Product();
        $prod1->persist();
        $prod = new Product();
        $prod->persist();
        $package = new Package();
        $package->Product = $prod;
        $package->persist();

        $products = ProductList::findProductsWithPackages();
        $this->assertCount(1, $products);
    }
}
