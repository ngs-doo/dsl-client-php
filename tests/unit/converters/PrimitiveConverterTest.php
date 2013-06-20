<?php
use NGS\Converter\PrimitiveConverter;

class PrimitiveConverterTest extends BaseTestCase
{
    public function testToMap()
    {
        $map = array(
            'key' => 'val',
            'key2' => 'val2'
        );
        $this->assertSame($map, PrimitiveConverter::toMap($map));
    }
}
