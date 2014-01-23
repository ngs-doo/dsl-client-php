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
     * @temp-depends testHistory
     */
    public function testMultipleHistory()
    {
        $first =  new Foo();
        $first->bar = (string)\NGS\UUID::v4();
        $first->persist();
        $first->delete();
        
        $second =  new Foo();
        $second->bar = (string)\NGS\UUID::v4();
        $second->persist();
        $secondv2 = clone $second;
        $secondv2->num = 3;
        $secondv2->persist();

        $uris = array($first->URI, $second->URI);
        
        $snapshots = Foo::getHistory(array($first->URI, $second->URI));
        
        $this->assertCount(2, ($snapshots));
        $this->assertCount(2, ($snapshots[0]));
        $this->assertCount(2, ($snapshots[1]));
        
        $this->assertEquals('INSERT', $snapshots[0][0]->action);
        $this->assertEquals('DELETE', $snapshots[0][1]->action);
        $this->assertEquals($snapshots[0][0]->value, $snapshots[0][1]->value);
        
        $this->assertEquals('INSERT', $snapshots[1][0]->action);
        $this->assertEquals('UPDATE', $snapshots[1][1]->action);
        $this->assertEquals($second, $snapshots[1][0]->value);
        $this->assertEquals($secondv2, $snapshots[1][1]->value);
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
