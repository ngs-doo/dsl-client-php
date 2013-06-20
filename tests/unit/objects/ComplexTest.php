<?php

use Img\Icon;
use Img\Bitmap;

/**
 * Test constructors
 */
class ComplexTest extends PHPUnit_Framework_TestCase
{
    static $provider = null;

    public static function instanceIcon()
    {
        return new Icon(array(
            'code'          => 42,
            'description'   => 'php rulz',
            'bitmask'       => array(true, false, true, false),
            'polygon'       => array(1.3, 2.1234, 3.14)
            )
        );
    }

    public static function providerArray()
    {
        return array(
            array(
                array(
                    'primary'    => self::instanceIcon(),
                    'secondary'  => null,
                    'auxylliary' => array(self::instanceIcon(), new Icon(array())),
                    'accessory'  => null
                )
            )
        );
    }

    public function providerInstance()
    {
        return array(
            array(
                new Bitmap(array(
                    'primary'    => self::instanceIcon(),
                    'secondary'  => null,
                    'auxylliary' => array(self::instanceIcon(), new Icon(array())),
                    'accessory'  => null)
                )
            )
        );
    }

    public static function providerInvalidArgs()
    {
        return array(
            array(null),
            array('some string'),
            array(true),
            array(false)
        );
    }

    public function testConstructNoParams()
    {
        // should be valid
        new Bitmap();
    }

    /**
     * @dataProvider providerInvalidArgs
     * @expectedException InvalidArgumentException
     */
    public function testConstructFromInvalidArgs()
    {
        new Bitmap('asdas');
        new Bitmap(false);
    }

    public function testConstructFromEmptyArray()
    {
        $foo = new Bitmap(array());

        $this->assertEquals($foo->primary, new Icon(array()));
        $this->assertEquals($foo->secondary, null);
        $this->assertEquals($foo->auxylliary, array());
        $this->assertEquals($foo->accessory, null);
    }

    /**
     * @dataProvider providerArray
     */
    public function testConstructFromArray($arr)
    {
        $foo = new Bitmap($arr);
        foreach($arr as $key=>$val) {
            $this->assertEquals($val, $foo->$key);
        }
        return $foo;
    }

    /**
     * @dataProvider providerArray
     */
    public function testConstructWithExtraArgs($arr)
    {
        $arr['some_extra_arg'] = '123';
        new Bitmap($arr);

        \NGS\Utils::WarningsAsErrors(true);

        $this->setExpectedException('InvalidArgumentException');
        new Bitmap($arr);
    }

    /**
     * @dataProvider providerArray
     */
    public function testToArrayMethod(array $arr)
    {
        $bitmap = new Bitmap($arr);
        $bitmapArray = $bitmap->toArray();
        // remove read-only properties
        unset($bitmapArray['URI']);
        unset($bitmapArray['ID']);
        $newBitmap = new Bitmap($bitmapArray);

        $this->assertEquals($bitmap, $newBitmap);
    }

    /**
     * @dataProvider providerInstance
     */
    public function testIsset(\Img\Bitmap $bitmap)
    {
        $this->assertTrue(isset($bitmap->primary));

        $bitmap->secondary = null;
        $this->assertFalse(isset($bitmap->secondary));
    }

    /**
     * @dataProvider providerInstance
     */
    public function testClone($foo)
    {
        $cloned = clone $foo;

        $this->assertNotSame($cloned, $foo);
        $this->assertSame(serialize($cloned), serialize($foo));
        $this->assertEquals($cloned, $foo);
    }

    /**
     * @dataProvider providerInstance
     * @expectedException LogicException
     */
    public function testUnsetNotNullProperty($foo)
    {
        unset($foo->primary);
    }
}
