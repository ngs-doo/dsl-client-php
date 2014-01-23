<?php

use NGS\UUID;

/**
 * Test constructors
 */
class UUIDTest extends PHPUnit_Framework_TestCase
{
    public static function providerUidsV4()
    {
        return array(
            array('6a531a5f-854e-4f76-91dc-7ea84c568f48'),
            array('c666a865-a0d0-43e5-b5c8-280cdfc80603'),
            array('a5ccc752-2f9f-4fba-8104-6c4fb3d8a5cf'),
        );
    }

    public static function invalidSources()
    {
        return array(
            array('invalid'),
            array(array()),
            array('5ccc752-2f9f-4fba-8104-6c4fb3d8a5cf'),
            array(false),
            array(true),
            array(12345),
        );
    }

    public function testConstructV4()
    {
        $foo = UUID::v4();
        $bar = new UUID($foo);

        $this->assertSame($foo->value, $bar->value);
    }

    public function testIsValid()
    {
        $this->assertTrue(UUID::isValid('6a531a5f-854e-4f76-91dc-7ea84c568f48'));
        $this->assertTrue(UUID::isValid('c666a865-a0d0-43e5-b5c8-280cdfc80603'));

        $invalidTypes = array(
            array(), true, false, 123, 1.2, new \stdClass()
        );
        foreach ($invalidTypes as $type) {
            try {
                UUID::isValid($type);
            }
            catch (\Exception $ex) {
                $this->assertTrue($ex instanceof InvalidArgumentException);
            }
        }

        $this->assertFalse(UUID::isValid('c666a865-a0d0-43e5-b5c8-280cdfc8060'));
        $this->assertFalse(UUID::isValid(''));
    }

    public function testConstructDefault()
    {
        $uuid = new UUID();
        $this->assertTrue(UUID::isValid($uuid->value));
    }

    /**
     * @dataProvider providerUidsV4
     */
    public function testToString($val)
    {
        $uuid = new UUID($val);
        $this->assertSame($val, (string)$uuid);
    }

    /**
     * @dataProvider InvalidSources
     * @expectedException InvalidArgumentException
     */
    public function testConstructInvalid($val)
    {
        $foo = new UUID($val);
    }
    
    public function testToArray()
    {
        $strings = array_map(function($val) { return $val[0]; } , $this->providerUidsV4());
        $uuids = UUID::toArray($strings);
        for($i=0; $i<count($strings); $i++) {
            $this->assertSame($strings[$i], $uuids[$i]->value);
        }
    }
}
