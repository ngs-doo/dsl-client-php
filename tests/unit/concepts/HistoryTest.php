<?php
use Test\Foo;
use NGS\Client\StandardProxy;
use NGS\Patterns\Snapshot;

class HistoryTest extends \BaseTestCase
{
    public function setUp()
    {
        $this->deleteAll('Test\Foo');
    }

    public function testHistory()
    {
        $foo =  new Foo();

        $foo->bar = (string)\NGS\UUID::v4();
        $foo->persist();

        $newFoo = clone $foo;
        $newFoo->num = 5;
        $newFoo->persist();

        $newFoo->delete();

        $history = Foo::getHistory($foo->URI);

        $this->assertEquals('INSERT', $history[0]->action);
        $this->assertEquals($foo, $history[0]->value);

        $this->assertEquals('UPDATE', $history[1]->action);
        $this->assertEquals($newFoo, $history[1]->value);

        $this->assertEquals('DELETE', $history[2]->action);
        $this->assertEquals($newFoo, $history[2]->value);

        return $history;
    }

    /**
     * @depends testHistory
     */
    public function testHistorySnapshots(array $snapshots)
    {
        foreach ($snapshots as $snapshot) {
            $this->assertTrue($snapshot instanceof Snapshot);

            $this->assertTrue($snapshot->at instanceof \NGS\Timestamp);
            $this->assertTrue($snapshot->value instanceof Foo);

            $this->assertEquals($snapshot->getAt(), $snapshot->at);
            $this->assertEquals($snapshot->getAction(), $snapshot->action);
            $this->assertEquals($snapshot->getValue(), $snapshot->value);
        }
    }

    /**
     * @depends testHistory
     * @expectedException InvalidArgumentException
     */
    public function testHistorySnapShotInvalidGetter(array $snapshots)
    {
        $snapshots[0]->not_a_property;
    }
}
