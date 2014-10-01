<?php
use NGS\Client\DomainProxy;
use Test\Foo\DoNothing;
use Test\Foo;
use Test\Foo\AddNum;

class AggregateEventTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->root = new Foo();
        $this->root->bar = time().rand(1,getrandmax());
        $this->root->num = 42;
        $result = $this->root->persist();
        $this->assertSame($this->root, $result);
    }

    public function tearDown()
    {
        $this->root->delete();
    }
/*
    public function testEventAsRootMethod()
    {
        $this->markTestSkipped('cannot run aggregate domain events due to lack of server-side permissions');

        $foo = $this->root->AddNum(array('amount'=>10));
        $this->assertSame(52, $foo->num);

        $newFoo = Foo::find($foo->URI);
        $this->assertSame(52, $newFoo->num);
    }

    public function testEventSubmitRoot()
    {
        $this->markTestSkipped('cannot run aggregate domain events due to lack of server-side permissions');

        $event = new AddNum(array('amount'=>10));
        $newRoot = $event->submit($this->root);
        $this->assertSame(52, $newRoot->num);
    }

    public function testEventSubmitString()
    {
        $this->markTestSkipped('cannot run aggregate domain events due to lack of server-side permissions');

        $event = new AddNum(array('amount'=>10));
        $newRoot = $event->submit($this->root->URI);
        $this->assertSame(52, $newRoot->num);
    }

    public function testAggregateRootApplyMethod()
    {
        $this->markTestSkipped('cannot run aggregate domain events due to lack of server-side permissions');

        $event = new AddNum(array('amount'=>10));
        $newRoot = $this->root->apply($event);
        $this->assertSame(52, $newRoot->num);
    }

    public function testDomainProxySubmit()
    {
        $this->markTestSkipped('cannot run aggregate domain events due to lack of server-side permissions');

        $event = new AddNum();
        $event->amount = 20;
        $proxy = DomainProxy::instance();
        $result = $proxy->submitAggregateEvent($event, $this->root->URI);

        $this->assertSame(62, $result->num);
    }
*/
    /**
     * @expectedException LogicException
     */
    public function testEventOnNonPersistedRoot()
    {
        $foo = new Foo();
        $foo->AddNum(array('amount'=>56));
    }
}
