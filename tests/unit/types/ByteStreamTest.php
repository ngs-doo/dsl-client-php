<?php

use NGS\ByteStream;
use Test\File;

/**
 * Test constructors
 */
class ByteStreamTest extends BaseTestCase
{
    static $provider = null;

    public function setUp()
    {
        $this->deleteAll('Test\File');
    }

    public static function instance()
    {
        return new ByteStream();
    }

    public static function providerRawString()
    {
        return array(
            array('abc'),
            array('ĐŠL;ĆČĆK=#ŠIJĆČĆ:'),
            array(''),
        );
    }

    public static function providerBase64()
    {
        return array_map(function($set) {
            return array(base64_encode($set[0]));
        }, self::providerRawString());
    }

    public static function providerInvalidValues()
    {
        return array(
            array( true ),
            array( false ),
            array( array() ),
            array( new stdClass() ),
        );
    }

    /**
     * @dataProvider providerRawString
     */
    public function testConstructFromStringAndInstance($str)
    {
        $foo = new ByteStream($str);
        $bar = new ByteStream($foo);

        $this->assertSame($str, $foo->getValue());
        $this->assertEquals($foo, $bar);
    }

    public function testConstructFromStream()
    {
        $file = $this->getFile('test.txt');
        $stream = fopen($file, 'r');
        $bs = new ByteStream($stream);

        $content = file_get_contents($file);
        $this->assertSame($content, $bs->value);
    }

    /**
     * @dataProvider providerRawString
     */
    public function testFromBinaryFactory($str)
    {
        $foo = ByteStream::fromBinary($str);
        $bar = ByteStream::fromBinary(new ByteStream($str));
        $this->assertEquals($foo, $bar);
    }

    /**
     * @dataProvider providerBase64
     */
    public function testFromBase64Factory($base64)
    {
        $foo = ByteStream::fromBase64($base64);
        $bar = ByteStream::fromBase64(new ByteStream(base64_decode($base64)));
        $this->assertEquals($foo, $bar);
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider providerInvalidValues
     */
    public function testConstructFromInvalidType($invalidValue)
    {
        new ByteStream($invalidValue);
    }

    /**
     * @dataProvider providerRawString
     */
    public function testEquals($str)
    {
        $foo = new ByteStream($str);
        $bar = new ByteStream($str);

        $this->assertTrue($foo->equals($bar));
        $this->assertNotSame($foo, $bar);
    }

    /**
     * @dataProvider providerRawString
     */
    public function testBase64Method($str)
    {
        $foo = new ByteStream($str);
        $this->assertSame(base64_encode($str), $foo->toBase64());
    }

    /**
     * @dataProvider providerRawString
     */
    public function testByteStreamAsPropertyFromJson($str)
    {
        $file = new File();
        $file->Content = new ByteStream($str);

        $json = $file->toJson();
        $fileFromJson = File::fromJson($json);

        $this->assertSame($file->Content->value, $fileFromJson->Content->value);
    }

    public function testPersistRootWithByteStreamProperty()
    {
        $string = 'abcđščžč';
        $file = new File();
        $file->Content = base64_encode($string);

        $file->persist();
        $fileFromServer = File::find($file->URI);

        $this->assertSame($string, $fileFromServer->Content->value);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMagicGetterWithInvalidProperty()
    {
        $bs = new ByteStream();
        $bs->invalidPropertyvalue;
    }

    public function testToArray()
    {
        $sources = array(
            'abc',
            'ĐŠL;ĆČĆK=#ŠIJĆČĆ:',
            '',
            new ByteStream(),
            new ByteStream('1@"€¶$bšćžčšz8e'),
        );
        $base64Sources = array_map(function($v) { return is_string($v) ? base64_encode($v) : $v; }, $sources);

        $bs = ByteStream::toArray($base64Sources);

        for($i=0; $i<count($bs); $i++) {
            $source = $sources[$i];
            $sourceValue = $source instanceof ByteStream ? $source->getValue() : $source;
            $this->assertSame($sourceValue, $bs[$i]->value);
        }
    }

    public function invalidMethodCalls()
    {
        return array(
            array( function () { ByteStream::fromBase64(123); } ),
            array( function () { ByteStream::fromBase64('this string is not valid base64'); } ),
            array( function () { ByteStream::fromBinary(false); } ),
            array( function () { ByteStream::toArray(array(null)); } ),
            array( function () { ByteStream::toArray(array(false)); } ),
            array( function () { ByteStream::toArray(array('not base64')); } ),
        );
    }

    /**
     * @dataProvider invalidMethodCalls
     * @expectedException InvalidArgumentException
     */
    public function testMethodCallsWithInvalidArguments($callable)
    {
        $bs = new ByteStream();
        $callable();
    }
}
