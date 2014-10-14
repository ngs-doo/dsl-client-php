<?php
use Img\Extension;
use Img\Bitmap;
use Img\Icon;
use Img\Album;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    public function testSetEnumPropertyOnEntity()
    {
        $img = new Bitmap();
        $img->ext = Img\Extension::jpg;

        $this->assertSame(Img\Extension::jpg, $img->ext);

        $img->persist();
        $persisted = Bitmap::find($img->URI);

        $this->assertSame(Img\Extension::jpg, $persisted->ext);
    }

    public function testSetEnumPropertyOnValue()
    {
        $val = new Icon();
        $val->ext = Img\Extension::png;

        $this->assertSame(Img\Extension::png, $val->ext);
    }

    public function testEnumPropertyDefaultValue()
    {
        $img = new Bitmap();
        $this->assertSame(Img\Extension::jpg, $img->ext);
    }

    public function testEnumGetAllValues()
    {
        $expected = array('jpg', 'png', 'bmp');
        $this->assertSame($expected, Extension::getValues());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetInvalid()
    {
        $img = new Icon();

        $img->ext = 'invalid-enum';
    }

    public function testCollectionProperty()
    {
        $album = new Album();
        $assigned = array('jpg', 'bmp');
        $album->allowed = $assigned;

        $album->persist();

        $found = Album::find($album->URI);

        $this->assertSame($assigned, $found->allowed);
    }
}
