<?php

use NGS\Patterns\Rest\HttpProxy;
use NGS\Patterns\Rest\CrudProxy;
use NGS\Client\Exception\NotFoundException;
use Test\Foo;
use Test\EntityTest;
use Test\EntityTest1;
use Test\RootWithEntity;
use Test\ValueTest;
use NGS\Client\StandardProxy;

class CrudTest extends BaseTestCase //\PHPUnit_Framework_TestCase
{

    public function rootProvider()
    {
        return array(
            array(
                new Foo(array(
                    'bar' => 'test'))
            ),
            array(
                new RootWithEntity(array(
                    'name' => 'test',
                    'ent'  => new EntityTest(array(
                        'name' => 'test',
                        'strArr' => array('a', 'b', 'asdfas'),
                        'intArr' => array('1', '2', '123456789'),
                    )),
                    'entarr'=> array(
                        new EntityTest1(array('name'=>'a')),
                        new EntityTest1(array(
                            'name'=> 'b',
                            'val' => new ValueTest(array('name'=>'adsf'))
                        ))
                    )
                ))
            ),
        );
    }

    public function setUp()
    {
        $this->deleteAll('Test\RootWithEntity');
    }

    /**
     * @dataProvider rootProvider
     */
    public function testCRUD($root)
    {
        $class = get_class($root);

        try {
            $item = $class::find('test');
            if ($item === null) {
                $this->fail('TODO find with no exception?');
            }

            $item->delete();
            $this->assertFalse($class::exists('test'));
        } catch (NotFoundException $e) {

        }

        $root->persist();
        $this->assertSame('test', $root->URI);
        $this->assertTrue($class::exists('test'));

        $foundRoot = $class::find('test');
        $this->assertEquals($root, $foundRoot);

        $root->delete();
        $this->assertFalse($class::exists('test'));
    }

    public function testEntityUriPropertyIsChangedAfterRootUpdate()
    {
        $i =  Foo::findAll();
        foreach($i as $it)
            $it->delete();

        $foo = new Foo();
        $foo->bar = 'old_uri';
        $foo->persist();

        $foo->bar = 'new_uri';
        $foo->persist();

        $this->assertSame('new_uri', $foo->URI);
    }

    public function testUpdateEntityUriOnPersist()
    {
        $root = new RootWithEntity(array(
            'name' => 'test',
            'ent' => new EntityTest(array(
                'name' => 'a'
            ))
        ));
        $root->persist();
        $foundRoot = RootWithEntity::find('test');
        $this->assertSame($root->ent->URI, $foundRoot->ent->URI);
    }


    public function testFindNonExistingArray()
    {
        $this->assertSame(array(), Foo::find(array('non-existing-uri', 'some-non-existing-uri')));
    }

    /**
     * @expectedException NGS\Client\Exception\NotFoundException
     */
    public function testFindNonExistingSingle()
    {
        $this->assertSame(array(), Foo::find('non-existing-uri'));
    }

    // IIS problems with uris including dots
    public function testUriEndingWithDots()
    {
        $uri ='..';
        try {
            $item = Foo::find($uri);
            if ($item === null)
                $this->markTestSkipped('TODO find should throw exception');
            $item->delete();
        }
        catch (NotFoundException $ex) {
        }

        $foo = new Foo();
        $foo->bar = $uri;
        $foo->persist();

        $item = $foo->find($uri);

        $this->assertEquals($foo, $item);
        $foo->delete();
    }

    public function testMultipleInsertUpdateDelete()
    {
        $proxy = new StandardProxy();

        $proxy->delete(Foo::findAll());

        $items = array(
            new Foo(array('bar'=>'a', 'num'=>1)),
            new Foo(array('bar'=>'b', 'num'=>5))
        );

        $uris = $proxy->insert($items);

        $inserted = Foo::findAll();

        $inserted[0]->num = 10;
        $inserted[1]->num = 100;

        $proxy->update($inserted);

        $this->assertSame(10, Foo::find('a')->getNum());
        $this->assertSame(100, Foo::find('b')->getNum());

        $proxy->delete($inserted);
    }
}
