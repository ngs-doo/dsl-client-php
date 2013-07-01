<?php

use Test\Foo;
use Test\FooReport;
use NGS\Client\DomainProxy;
use NGS\Client\StandardProxy;
use NGS\Client\ReportingProxy;
use NGS\Patterns\Templater;

class TemplaterTest extends \BaseTestCase
{
    protected $items = array();

    protected function setUp()
    {
        $proxy = new StandardProxy();
        $proxy->delete(Foo::findAll());

        $this->items[] = new Foo(array('bar'=>'a', 'num'=>1));
        $this->items[] = new Foo(array('bar'=>'b', 'num'=>2));
        $this->items[] = new Foo(array('bar'=>'c', 'num'=>3));
        $proxy->insert($this->items);
    }

    protected function tearDown()
    {
        $this->deleteAll('Test\Foo');
    }

    public function testTemplaterFind()
    {
        $expected = '<foo><bar>b</bar><num>2</num></foo>';

        $templater = new Templater('template.txt', 'Test\\Foo');
        $content = $templater->find('b');
        $this->assertSame($expected, $content);
    }

    public function testReportingProxyFindTemplater()
    {
        $expected = '<foo><bar>a</bar><num>1</num></foo>';

        $proxy = new ReportingProxy();
        $result = $proxy->findTemplater('template.txt', 'Test\\Foo', 'a');
        $this->assertSame($expected, $result);
    }

    public function testReportingProxyFindTemplaterWithoutURI()
    {
        $expected = '<foo><bar>a</bar><num>1</num></foo>'."\r\n".
                    '<foo><bar>b</bar><num>2</num></foo>'."\r\n".
                    '<foo><bar>c</bar><num>3</num></foo>';

        $proxy = new ReportingProxy();
        $result = $proxy->findTemplater('template.txt', 'Test\\Foo');
        $this->assertSame($expected, $result);
    }

    public function testReportingProxySearchTemplater()
    {
        $expected = '<foo><bar>c</bar><num>3</num></foo>';

        $proxy = new ReportingProxy();
        $spec = new \Test\Foo\greaterThan(array('min' => 2));
        $result = $proxy->searchTemplater('template.txt', $spec);
        $this->assertSame($expected, $result);
    }

    public function testTemplaterSearch()
    {
        $expected = '<foo><bar>c</bar><num>3</num></foo>';
        $templater = new Templater('template.txt', 'Test\\Foo');
        $spec = new \Test\Foo\greaterThan(array('min' => 2));
        $content = $templater->search($spec);
        $this->assertSame($expected, $content);
    }

    public function testTemplaterGenericSearch()
    {
        $expected = '<foo><bar>c</bar><num>3</num></foo>';

        $templater = new Templater('template.txt');
        $search = new NGS\Patterns\GenericSearch('Test\\Foo');
        $search->gt('num', 2);

        $content = $templater->search($search);
        $this->assertSame($expected, $content);
    }

    public function testTemplaterOlapCube()
    {
        $expected = '<foo><bar>a</bar><num>[[num]]</num></foo>'."\r\n".
                    '<foo><bar>b</bar><num>[[num]]</num></foo>'."\r\n".
                    '<foo><bar>c</bar><num>[[num]]</num></foo>';
        $dimensions = array('bar');
        $facts = array('count', 'total', 'average');
        $order = array('bar' => true);
        $cube = new \Test\FooCube();

        $result = $cube->createXml($dimensions, $facts, $order);
        $this->assertSame($expected, $result);
    }

    public function testTemplaterOlapCubeWithSpecification()
    {
        $dimensions = array('bar', 'num');
        $facts = array('count', 'total', 'average');
        $cube = new \Test\FooCube();
        $spec = new \Test\FooCube\findByBar();
        $spec->query = 'a';
        
        $content = $cube->createXml($dimensions, $facts, array(), $spec);

        $expected = '<foo><bar>a</bar><num>1</num></foo>';
        $this->assertSame($expected, $content);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTemplaterSearchInvalidType()
    {
        $templater = new Templater('template.txt');
        $content = $templater->search(false);
    }
}
