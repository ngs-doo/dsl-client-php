<?php

class MapTest extends \BaseTestCase
{
    public function testSerializeEmptyMapProperty()
    {
        // @todo empty map is serialized as '[]', should be '{}'
        $t = new Test\Typer();
        $expected = '{"URI":null,"ID":0,"map":{}}';
        $this->assertSame($expected, $t->toJson());
    }

    public function testMapPersistDefaultValue()
    {
        // @todo Data to create not specified: empty map is serialized as '[]', should be '{}'
        $t = new Test\Typer();
        $t->persist();
        $this->assertSame(array(), $t->map);
    }

    public function testMapPersist()
    {
        $map = array(
            'string'    => 'string',
            'string1'   => 'string2',
            'empty'     => '',
            ''          => 'empty',
        );
        $t = new Test\Typer();
        $t->map = $map;
        $t->persist();

        ksort($map);
        $found =  Test\Typer::find($t->URI);
        $this->assertSame($map, $found->map);
        // $this->assertSame($map, $t->map);
    }

}
