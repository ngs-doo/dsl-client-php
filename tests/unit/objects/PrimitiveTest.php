<?php

use \Img\Icon;

/**
 * Test constructors
 */
class PrimitiveTest extends PHPUnit_Framework_TestCase
{

    public static function providerArray()
    {
        return array(
            array(
                array(
                    'code'          => 42,
                    'description'   => 'php rulz',
                    'bitmask'       => array(true, false, true, false),
                    'polygon'       => array(1.3, 2.1234, 3.14)
                )
            ),
            array(
                array(
                    'code'          => 0,
                    'description'   => null,
                    'bitmask'       => null,
                    'polygon'       => array()
                )
            ),
            array(
                array(
                    'code'          => 0,
                    'description'   => '',
                    'bitmask'       => array(),
                    'polygon'       => array()
                )
            )
        );
    }

    public static function providerInstance()
    {
        $arr = self::providerArray();
        $res = array();
        foreach($arr as $val){
            $res[] = array(new Icon($val[0]));
        }
        return $res;
    }

    public function testConstructNoParams()
    {
        // should be valid
        $foo = new Img\Icon();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructFromInvalidArgs()
    {
        $foo = new Icon('asdas');
    }

    public function testConstructFromEmptyArray()
    {
        $foo = new Img\Icon(array());

        $this->assertSame(0, $foo->code);
        $this->assertSame(null, $foo->description);
        $this->assertSame(null, $foo->bitmask);     // bitmask can be null
        $this->assertSame(array(), $foo->polygon);  // polygon not null
    }

    /**
     * @dataProvider providerArray
     */
    public function testConstructFromArray($arr)
    {
        $foo = new Img\Icon($arr);
        $this->assertSame($arr['description'], $foo->description);
        $this->assertSame($arr['code'], $foo->code);
        $this->assertSame($arr['bitmask'], $foo->bitmask);

        return $foo;
    }

    /**
     * @dataProvider providerArray
     */
    public function testConstructWithExtraArgs($arr)
    {
        \NGS\Utils::WarningsAsErrors(false);
        $arr['some_extra_arg'] = '123';
        new Icon($arr);

        \NGS\Utils::WarningsAsErrors(true);
        $this->setExpectedException('InvalidArgumentException');
        new Icon($arr);
    }

    /**
     * @dataProvider providerArray
     */
    public function testToArray(array $arr)
    {
        $icon = new Icon($arr);
        $this->assertSame($arr, $icon->toArray());
    }

    /**
     * @dataProvider providerInstance
     * @expectedException InvalidArgumentException
     */
    public function testSetInvalidType(Img\Icon $icon)
    {
        $icon->description = true;
    }

    /**
     * @dataProvider providerInstance
     * @expectedException InvalidArgumentException
     */
    public function testSetNonNullToNull(Img\Icon $icon)
    {
        $icon->code = null;
    }

    /**
     * @dataProvider providerInstance
     * @expectedException InvalidArgumentException
     */
    public function testSetInvalidTypeInArray(Img\Icon $icon)
    {
        $icon->bitmask = array(true, false, "this is not a bool");
    }

    /**
     * @dataProvider providerInstance
     */
    public function testSetters(Img\Icon $icon)
    {
        $icon->description = 'bla';
        $this->assertSame('bla', $icon->description);

        $icon->code = 123;
        $this->assertSame(123, $icon->code);

        $icon->bitmask = array(false, false, true);
        $this->assertSame(array(false, false, true), $icon->bitmask);
    }

    public function testToString()
    {
        $values = array(
            'code'          => 42,
            'description'   => 'php rulz',
            'bitmask'       => array(true, false),
            'polygon'       => array(1.5)
        );
        $res = 'Img\Icon{"code":42,"description":"php rulz","bitmask":[true,false],"polygon":[1.5]}';
        $this->assertSame($res, (string) new Icon($values));
    }

    /**
     * @dataProvider providerInstance
     */
    public function testUnsetNullableProperty(Img\Icon $icon)
    {
        unset($icon->description);
        unset($icon->bitmask);

        $this->assertSame(null, $icon->description);
        $this->assertSame(null, $icon->bitmask);
    }

    /**
     * @dataProvider providerInstance
     * @expectedException LogicException
     */
    public function testUnsetNotNullProperty(Img\Icon $icon)
    {
        unset($icon->code);
    }

    /**
     * @dataProvider providerArray
     */
    public function testIsset(array $arr)
    {
        $icon = new Icon($arr);

        $this->assertSame(true, isset($icon->code));

        $this->assertSame(false, isset($icon->non_existing_property));

        $icon->description = null;
        $this->assertSame(false, isset($icon->description));
    }

        /**
     * @dataProvider providerArray
     */
    public function testClone($arr)
    {
        $icon = new Icon($arr);
        $cloned = clone $icon;
        $this->assertNotSame($cloned, $icon);
    }

}
