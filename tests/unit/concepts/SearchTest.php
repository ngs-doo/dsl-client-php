<?php
use NGS\Client\StandardProxy;
use Test\FooFoo;
use Test\FooFoo\searchByBar;
use NGS\Client\Exception\NotFoundException;
use NGS\Patterns\GenericSearch;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    protected $items = array();

    protected function setUp()
    {
        StandardProxy::instance()->delete(FooFoo::findAll());

        $this->items = array(
            new FooFoo(array('bar' => 'a',  'num' => 1)),
            new FooFoo(array('bar' => 'ab', 'num' => 2)),
            new FooFoo(array('bar' => 'c',  'num' => 2)),
            new FooFoo(array('bar' => 'b',  'num' => 3))
        );
        StandardProxy::instance()->insert($this->items);
    }

    protected function tearDown()
    {
        StandardProxy::instance()->delete(FooFoo::findAll());
    }

    public function testSearchWithLimitAndOffset()
    {
        return ;

        $spec = new searchByBar();
        $spec->name = 'a';

        $items = $spec->builder()
            ->limit(1)
            ->offset(1)
            ->search();

        $this->assertSame(1, count($items));
        $this->assertEquals('ab', $items[0]->bar);
    }

    public function testSearchWithMultipleOrder()
    {
        $spec = new searchByBar();

        $items = $spec->builder()
            ->desc('num')
            ->asc('bar')
            ->search();

        $this->assertSame($items[0]->URI, 'b');
        $this->assertSame($items[1]->URI, 'ab');
        $this->assertSame($items[2]->URI, 'c');
        $this->assertSame($items[3]->URI, 'a');
    }

    public function testGetttersAndSettersForSpecificationProperties()
    {
        $spec = new searchByBar();
        $builder = $spec->builder();
        $builder->name = 'c';

        $this->assertSame('c', $builder->name);
    }

    public function testAliasMethods()
    {
        $spec = new searchByBar();
        $res1 = $spec->builder()
            ->descending('num')
            ->ascending('bar')
            ->take(2)
            ->skip(1)
            ->search();

        $res2 = $spec->builder()
            ->desc('num')
            ->asc('bar')
            ->limit(2)
            ->offset(1)
            ->search();

        $this->assertSame(2, count($res1));
        $this->assertEquals($res1, $res2);
    }

    public function testGenericSearch()
    {
        $search = new NGS\Patterns\GenericSearch('Test\\FooFoo');
        $search
            ->moreThan('num', 1)
            ->lessThan('num', 3);

        $results = $search->search();

        $this->assertSame(2, $search->count());

        $this->assertSame(2, count($results));
        $this->assertSame('ab', $results[0]->bar);
    }

    public function testGenericSearchWithNoFilters ()
    {
        $search = new NGS\Patterns\GenericSearch('Test\\FooFoo');
        $results = $search->search();
        $this->assertSame(4, $search->count());
        $this->assertSame(4, count($results));
    }

    public function testGenericSearchWithLimitAndOffset()
    {
        return ;
        $search = new NGS\Patterns\GenericSearch('Test\\FooFoo');
        $search->gt('num', 1)->lt('num', 3);

        $results = $search->search();

        $this->assertSame(1, $search->count());

        $this->assertSame(1, count($results));
        $this->assertSame('ab', $results[0]->bar);
    }

    public function testCountOnSearchable()
    {
        $this->assertSame(4, FooFoo::count());

        $spec = new searchByBar(array('name'=>'a'));
        $this->assertSame(2, FooFoo::count($spec));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGenericSearchWithInvalidClass()
    {
        new GenericSearch('This\Class\Should\Not\Exist');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCallUndefinedFilter()
    {
        $search = new GenericSearch('Test\\FooFoo');
        $search->oops_invalid_filter('abc');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCallFilterWithoutParams()
    {
        $search = new GenericSearch('Test\\FooFoo');
        $search->gt();
    }

    public function testGetters()
    {
        $search = new GenericSearch('Test\\FooFoo');
        $this->assertSame('Test\\FooFoo', $search->getObject());

        $search->equals('num', 1);
        $search->notEquals('num', 2);
        $expectedFilters = array(
            'num' => array(
                array('Key'=>0, 'Value'=>'1'),
                array('Key'=>1, 'Value'=>'2'),
            )
        );
        $this->assertSame($expectedFilters, $search->getFilters());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUndefinedFilter()
    {
        $search = new GenericSearch('Test\\FooFoo');
        $search->callUndefinedFilter('bla', 1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFilterByUndefinedObjectProperty()
    {
        $search = new GenericSearch('Test\\FooFoo');
        $search->equals('undefined_property', 1);
    }
}
