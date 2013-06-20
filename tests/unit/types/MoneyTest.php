<?php

use NGS\Money;

require_once(__DIR__.'/BigDecimalTest.php');

/**
 * Test constructors
 */
class MoneyTest extends PHPUnit_Framework_TestCase
// extends BigDecimalTest
{
    public static function providerConstructorValues()
    {
        return array(
            array('12'),
            array(12),
            array(0),
            array(-13),
            array('12.2'),
            array('12.24'),
            array(1.1),
            array(1.10),
            array('-121212412.24'),
            array(new Money(1)),
        );
    }

    public static function providerInstances()
    {
        return array_map(function($a) { return array(new Money($a[0])); },
            self::providerConstructorValues());
    }

    public static function providerInvalid()
    {
        return array(
            // array('12.123'),
            array('12.14.42'),
            array(array()),
            array(true),
            array(false),
            array(null),
        );
    }

    /**
     * @dataProvider providerConstructorValues
     */
    public function testConstruct($value, $Scale=null)
    {
        $instance = new Money($value);
        $this->assertInstanceOf('NGS\Money', $instance);

        return $instance;
    }

    /**
     * @dataProvider providerInstances
     */
    public function testConstructFromInstance($value)
    {
        $new = new Money($value);
        $this->assertSame(0, $new->comp($value));

        return $value;
    }

    /**
     * @dataProvider providerInvalid
     * @expectedException InvalidArgumentException
     */
    public function testInvalid($value)
    {
        $foo = new Money($value);
    }
}
