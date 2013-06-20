<?php

use NGS\BigDecimal;

/**
 * Test constructors
 */
class BigDecimalTest extends PHPUnit_Framework_TestCase
{
    public static function providerConstructorValues()
    {
        return array(
            array('12', 4),
            array(12, 1),
            array(0, 0),
            array(-13, 7),
            array('12.24', 35),
            array('-121212412.24', 2),
        );
    }

    public static function providerInstances()
    {
        return array_map(function($a) { return array(new BigDecimal($a[0], $a[1])); },
            self::providerConstructorValues());
    }

    public static function providerInvalid()
    {
        return array(
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
        $instance = new BigDecimal($value, $Scale);
        $this->assertInstanceOf('NGS\BigDecimal', $instance);

        return $instance;
    }

    /**
     * @dataProvider providerInstances
     */
    public function testCompare($value)
    {
        $this->assertSame(0, $value->comp($value));
    }

    /**
     * @dataProvider providerInstances
     */
    public function testConstructFromInstance($value)
    {
        $new = new BigDecimal($value);
        $this->assertSame(0, $new->comp($value));
        return $value;
    }

    /**
     * @dataProvider providerInvalid
     * @expectedException InvalidArgumentException
     */
    public function testInvalid($value)
    {
        $foo = new BigDecimal($value);
    }

    /**
     * @dataProvider providerInstances
     */
    public function testAddAndSub($val)
    {
        $dec = $val->sub($val)->add($val);
        $this->assertEquals($dec->value, $val->value);
        $this->assertTrue($dec->comp($val)===0);
    }

    public function testToArrayWithScale()
    {
        $values = array(1, 0.1, '2.22', 3.333);

        $items = BigDecimal::toArrayWithScale($values, 2);
        $stringValues = array_map(function($val) {return $val->__toString();}, $items);

        $this->assertSame(array('1.00', '0.10', '2.22', '3.33'), $stringValues);
    }

    public function testScaleProperty()
    {
        $obj = new \Properties\decimalScaleRoot();

        $obj->prop = new \NGS\BigDecimal('0.11', 1);

        $obj->null_prop = new \NGS\BigDecimal('0.01', 22);

        $obj->prop_arr = array(
            new \NGS\BigDecimal('0.11', 3),
            new \NGS\BigDecimal('0.11', 1),
        );

        $obj->null_prop_arr_with_nulls = array(
            new \NGS\BigDecimal('0.11', 3),
            new \NGS\BigDecimal('0.11', 2),
            null,
        );

        $obj->prop_arr_with_nulls = array(
            new \NGS\BigDecimal('0.11', 3),
            new \NGS\BigDecimal('0.11', 2),
            null,
        );
        $this->assertEquals($obj, \Properties\decimalScaleRootArrayConverter::fromArray($obj->toArray()));
    }
}
