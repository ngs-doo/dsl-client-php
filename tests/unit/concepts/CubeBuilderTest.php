<?php
use Test\Foo;
use Test\FooGrid;
use Test\FooCube;
use NGS\Client\StandardProxy;
use NGS\Patterns\CubeBuilder;
use Test\FooCube\findByBar;

class CubeBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function invalidDimensionsAndFacts()
    {
        return array(
            array('undefined-dimension-or-fact'),
            array(false),
            array(true),
            array(-1),
            array(false),
            array(array()),
            array(new stdClass()),
        );
    }

    public function setUpPersist()
    {
        $proxy = new StandardProxy();
        $proxy->delete(Foo::findAll());

        $items = array(
            new Foo(array('bar'=>'d', 'num'=>1)),
            new Foo(array('bar'=>'c', 'num'=>2)),
            new Foo(array('bar'=>'ab', 'num'=>2)),
            new Foo(array('bar'=>'aa', 'num'=>3)),
        );
        $proxy = new StandardProxy();
        $proxy->insert($items);
    }

    public function tearDownPersist()
    {
        $proxy = new StandardProxy();
        $proxy->delete(Foo::findAll());
    }

    public function testCubeBuilder()
    {
        $this->setUpPersist();

        $cube = new FooCube();
        $builder = $cube->builder()
            ->facts(array('count'))
            ->add('total')
            ->dimension('num')
            ->desc('count')
            ->desc('total')
        ;
        $rows = $builder->analyze();

        $expectedRows = array(
            array('num' => 2, 'count' => 2, 'total' => 4),
            array('num' => 3, 'count' => 1, 'total' => 3),
            array('num' => 1, 'count' => 1, 'total' => 1)
        );
        $this->assertSame(3, count($rows));
        $this->assertSame($expectedRows, $rows);

        // with limit and offset
        $builder->limit(1)->offset(1);
        $rows2 = $builder->analyze();
        
        $this->assertSame(1, count($rows2));
        $this->assertSame(array($expectedRows[1]), $rows2); 
        
        $this->tearDownPersist();
    }

    public function testCubeBuilderWithSpecification()
    {
        $this->setUpPersist();

        $cube = new FooCube();
        $specification = new findByBar(array('query'=>'a'));

        $builder = $cube->builder()
            ->facts(array('count', 'total'))
            ->fact('average')
            ->add('total')
            ->with($specification)
        ;
        $rows = $builder->analyze();

        $this->assertSame(2, $rows[0]['count']);
        $this->assertSame(5, $rows[0]['total']);
        $this->assertSame(2.5, $rows[0]['average']);

        $this->tearDownPersist();
    }

    public function testAddMethods()
    {
        $b1 = new CubeBuilder(new FooCube());
        $b2 = new CubeBuilder(new FooCube());

        $b1->dimensions(array('bar', 'num'))
           ->facts(array('total'))
           ->ascending('bar');
        $b2->dimension('bar')
           ->add('num')
           ->add('total')
           ->ascending('bar');

        $this->assertEquals($b1, $b2);
    }

    /**
     * @dataProvider invalidDimensionsAndFacts
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDimensions($value)
    {
        $b = new CubeBuilder(new FooCube());
        $b->dimension($value);
    }

    /**
     * @dataProvider invalidDimensionsAndFacts
     * @expectedException InvalidArgumentException
     */
    public function testInvalidFacts($value)
    {
        $b = new CubeBuilder(new FooCube());
        $b->fact($value);
    }

    /**
     * @dataProvider invalidDimensionsAndFacts
     * @expectedException InvalidArgumentException
     */
    public function testInvalidAdd($value)
    {
        $b = new CubeBuilder(new FooCube());
        $b->add($value);
    }

        /**
     * @dataProvider invalidDimensionsAndFacts
     * @expectedException InvalidArgumentException
     */
    public function testInvalidAsc($value)
    {
        $b = new CubeBuilder(new FooCube());
        $b->asc($value);
    }
}
