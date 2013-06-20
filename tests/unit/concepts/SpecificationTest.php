<?php
use NGS\Client\StandardProxy;
use NGS\Client\DomainProxy;
use Test\Foo;
use Test\Foo\searchByBar;
use NGS\Client\Exception\NotFoundException;

class SpecificationTest extends BaseTestCase
{
    protected $items = array();

    protected function setUp()
    {
        StandardProxy::instance()->delete(Foo::findAll());

        $items = array(
            new Foo(array('bar'=>'abcd')),
            new Foo(array('bar'=>'abcd_2')),
            new Foo(array('bar'=>'c')),
            new Foo(array('bar'=>'b'))
        );
        foreach($items as $item) {
            try {
                $item = Foo::find($item->bar);
            }
            catch (NotFoundException $e) {
                $item->persist();
            }
            $this->items[] = $item;
        }
    }

    protected function tearDown()
    {
        StandardProxy::instance()->delete(Foo::findAll());
    }

    public function testSearchWithLimitAndOffset()
    {
        $items = Foo::searchByBar('abc');

        $this->assertSame(2, count($items));

        $limit = 1;
        $offset = 1;
        $items = Foo::searchByBar('abcd', $limit, $offset);

        $this->assertEquals(array($this->items[1]), $items);
    }

    public function testCountWithSpecification()
    {
        $spec = new searchByBar();
        $spec->name = 'ab';
        $this->assertSame(2, $spec->count());
    }

    public function testDomainProxyCount()
    {
        $proxy = DomainProxy::instance();
        $this->assertSame(4, $proxy->count('Test\Foo'));
    }

    public function testSearchSpecificationDefinedOnEntity()
    {
        $this->deleteAll('Blog\Post');

        $post = new \Blog\Post(array('Title'=>'abc'));
        $post->persist();

        $spec = new \Blog\Post\findByTitle();
        $spec->query = 'abc';

        $results = DomainProxy::instance()->searchWithSpecification('Blog\PostView', $spec);

        $expected = array(new \Blog\PostView(array(
            'URI'     => $post->URI,
            'Title'   => $post->Title,
            'Content' => $post->Content,
        )));
        $this->assertEquals($expected, $results);

        $post->delete();
    }
}
