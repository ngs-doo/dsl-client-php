<?php

use  Test\FooReport;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    protected $foo;

    protected function setUp()
    {
        $this->root = new \Test\Foo(array('bar'=>'test'));
        $this->root->persist();
    }

    protected function tearDown()
    {
        $this->root->delete();
    }

    public function testPopulate()
    {
        $foo = new \Test\FooReport(array('uri'=>'test'));
        $reportData = $foo->populate();
        $this->assertEquals($this->root, $reportData->foo);
    }

    public function testReportCreate()
    {
        $report = new FooReport(array('uri'=>'test'));
        $xml = $report->createXml();
    }
}
