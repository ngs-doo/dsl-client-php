<?php
use Test\SomeEvent;
use NGS\Client\DomainProxy;
use NGS\Client\StandardProxy;

class DomainEventTest extends BaseTestCase
{
    public function testEventSubmitMethod()
    {
        $event = new SomeEvent();
        $eventUri = $event->submit();

        $this->assertSame($eventUri, $event->URI);

        $proxy = new DomainProxy();
        $ev = $proxy->find('Test\SomeEvent', array($eventUri));

        $this->assertEquals($event, array_pop($ev));
    }

    public function testDomainSubmitEvent()
    {
        $proxy = new DomainProxy();
        $event = new SomeEvent();

        $uri = $proxy->submitEvent($event, $returnInstance=false);
        $this->assertNull($event->URI);
        $this->assertInternalType('string', $uri);

        $eventFromServer = $proxy->submitEvent($event, $returnInstance=true);
        $this->assertInstanceOf(get_class($event), $eventFromServer);
        $this->assertInternalType('string', $eventFromServer->URI);
    }
}
