<?php

use \NGS\Timestamp;

/**
 * Test constructors
 */
class TimestampTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        date_default_timezone_set('UTC');
    }

    public static function providerDates()
    {
        return array(
            array('2012-07-20T13:33:25.869343+00:00', 'Y-m-d\\TH:i:s.uP'),
            array('1012-11-30', 'Y-m-d'),
            array('0001', 'Y'),
            array('0001-01-01 12:15', 'Y-m-d H:i'),
            array('31.12.1999', 'd.m.Y', 'CET'),
            array('', null),  // valid
        );
    }

    public static function providerInvalidValues()
    {
        return array(
            array( true ),
            array( false ),
            array( array() ),
            array( new stdClass() ),
            array( 'aaa' ),
            array( null ),
            array( -1.234 ),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider providerInvalidValues
     */
    public function testConstructFromInvalidType($invalidValue)
    {
       $date = new Timestamp($invalidValue);
    }

    /**
     * @dataProvider providerDates
     */
    public function testConstructFromString($date, $format)
    {
        new Timestamp($date, $format);
    }

    public function testFormat()
    {
        $format = 'Y-m-d H:i';
        $date = date('Y-m-d H:i');
        $ts = new Timestamp($date, $format);

        $this->assertSame($date, $ts->format($format));
        $this->assertSame((string) $ts, $ts->format(Timestamp::STRING_FORMAT));
    }

    /**
     * @dataProvider providerDates
     */
    public function testConstructFromDateTime($value, $format, $timezone=Timestamp::DEFAULT_TIMEZONE)
    {
        $dt = \DateTime::createFromFormat($format, $value, new \DateTimeZone($timezone));
        $ts = new Timestamp($value, $format, $timezone);

        // comparing for equality...
        // $this->assertEquals($dt, $ts->toDateTime());
        // ...results in known bug https://bugs.php.net/bug.php?id=60873
        // 
        // --- Expected
        // +++ Actual
        // @@ @@
        //  DateTime Object (
        //      'date' => '2012-07-20 13:33:25'
        // -    'timezone_type' => 1
        // -    'timezone' => '+00:00'
        // +    'timezone_type' => 3
        // +    'timezone' => 'UTC'
        
        // ...rather compare string representation
        $zone = new \DateTimeZone('UTC');
        $format = 'Y-m-d h:i:s.u P';
        $this->assertEquals(
            $dt->setTimezone($zone)->format($format),
            $ts->toDateTime()->setTimezone($zone)->format($format));
    }

    /**
     * @dataProvider providerDates
     */
    public function testConstructFromLocalDate($dateString, $format)
    {
        $foo = new \NGS\LocalDate($dateString, $format);
        $bar = new Timestamp($foo);
        $this->assertSame($foo->format('Y-m-d'), $bar->format('Y-m-d'));
    }

    /**
     * @dataProvider providerDates
     */
    public function testConstructFromTimestamp($dateString, $format)
    {
        $ts = new Timestamp($dateString, $format);
        $ts2 = new Timestamp($ts);
        $this->assertEquals($ts, $ts2);
    }

    public function testEquals()
    {
        $foo = new Timestamp('2012-01-05', 'Y-m-d');
        $bar = new Timestamp('2012-01-05', 'Y-m-d');
        $bar2 = new Timestamp('2012-01-06', 'Y-m-d');

        $this->assertTrue($foo->equals($bar));
        $this->assertFalse($foo->equals($bar2));
    }

    public function testToInt()
    {
        $time = time();
        $ts = new Timestamp($time);
        $this->assertSame($time, $ts->toInt());
    }

    public function testToFloat()
    {
        $time = 123456.78912;
        $ts = new Timestamp($time);
        $this->assertSame($time, $ts->toFloat());
    }

    public function testToDateTime()
    {
        $ts = new Timestamp();
        $datetime = $ts->toDateTime();
        $this->assertTrue($ts->toDateTime() instanceof \DateTime);

        $this->assertEquals($ts, new Timestamp($datetime));
    }

    public function testCreateFromStringWithLongMicrosecondsFractions()
    {
        // fraction longer than 6 digits ('u' date format expects 6)
        // works up to 8 digits!!!
        $date = '2013-02-11T17:49:37.84827391+01:00';
        $ts = new Timestamp($date);
        $this->assertTrue($ts->toDateTime() instanceof \DateTime);

        // expected value fraction is 6 digits long
        $expected = '2013-02-11T16:49:37.848273+00:00';

//         $this->markTestIncomplete('fails on php 5.3.22');
        // fails on PHP 5.3.22
        //  --- Expected
        //  +++ Actual
        //  @@ @@
        //  -2013-02-11T17:49:37.848273+01:00
        //  +2013-02-11T17:49:37.848274+01:00

        $this->markTestIncomplete('last microseconds digit is not rounded consistently');

        // fails in php 5.3.22
        //1) TimestampTest::testCreateFromStringWithLongMicrosecondsFractions
        //Failed asserting that two strings are identical.
        //--- Expected
        //+++ Actual
        //@@ @@
        //-2013-02-11T17:49:37.848273+01:00
        //+2013-02-11T17:49:37.848274+01:00

        $this->assertSame($expected, (string)$ts);
    }

    public function testTimezonesToString()
    {
        $date = '2011-12-31';
        $ts = new Timestamp($date);
        $expected = '2011-12-31T00:00:00.000000+00:00';
        $this->assertSame($expected, $ts->__toString());
        $ts2 = new Timestamp($ts->__toString());
        $this->assertEquals($ts, $ts2);
    }

    public function testSetDefaultTimezone()
    {
        $date     = '2011-12-31T12:30:00.000000+05:00';
        $expected = '2011-12-31T07:30:00.000000+00:00';
        $ts = new Timestamp($date);
        $this->assertSame($expected, $ts->__toString());

        Timestamp::setDefaultTimezone(new \DateTimeZone('Asia/Tokyo'));
        $expected2 = '2011-12-31T16:30:00.000000+09:00';
        $ts2 = new Timestamp($date);
        $this->assertSame($expected2, $ts2->__toString());

        $ts3 = new Timestamp($expected2);
        $this->assertSame($expected2, $ts3->__toString());
    }

}
