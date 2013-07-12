<?php

class PropertiesTest extends PHPUnit_Framework_TestCase
{
    public function providerPrimitiveValues()
    {
        return array(
            array('string', 'string', array('test', '', ' đšćžč €¶| ')),
            array('float', 'float', array(0.0, 1.234, 1e5, -1.42e10)),
            array('float', 'double', array(0., 1.234, 1e5)),
            array('bool', 'bool', array(true, false)),
            array('int', 'int', array(0, 1, -123)),
            array('int', 'long', array(0, 1, -123)),
            array('string', 'stringWithLength', array('', 'abc', '1234567890')),
        );
    }

    public function providerComplexValues()
    {
        return array(
            array('NGS\LocalDate', 'date', array(
                new \NGS\LocalDate('2000-12-31'),
                new \NGS\LocalDate(),
            )),
            array('NGS\ByteStream', 'binary', array(
                new \NGS\ByteStream('abc'),
                new \NGS\ByteStream(),
            )),
            array('NGS\BigDecimal', 'decimal', array(
                new \NGS\BigDecimal(1.234),
                new \NGS\BigDecimal('0.00'),
                new \NGS\BigDecimal(),
            )),
            array('NGS\BigDecimal', 'decimalScale', array(
                new \NGS\BigDecimal(1.232, 2),
                new \NGS\BigDecimal('0.01', 2),
                new \NGS\BigDecimal(1, 2),
            )),
            array('NGS\UUID', 'guid', array(
                \NGS\UUID::v4(),
                new \NGS\UUID(),
            )),
            array('NGS\Money', 'money', array(
                new \NGS\Money(9.0),
                new \NGS\Money(1.23),
                new \NGS\Money('-1.22'),
            )),
            array('NGS\Timestamp', 'timestamp', array(
                new \NGS\Timestamp(),
                new \NGS\Timestamp('2011-12-31'),
            )),
        );
    }

    public function providerObjectValues()
    {
        return array(
            array('Properties\valueRoot', 'value', array(
                new \Properties\ValueObject(),
                new \Properties\ValueObject(array('str' => 'abc')),
            )),
        );
    }

    public function providerInvalidValues()
    {
        return array(
            array('string', 'string', array('test', '', ' đšćžč €¶| ')),
            array('float', 'float', array(0.0, 1.234, 1e5, -1.42e10)),
            array('float', 'double', array(0., 1.234, 1e5)),
            array('bool', 'bool', array(true, false)),
            array('int', 'int', array(0, 1, -123)),
            array('int', 'long', array(0, 1, -123)),
            array('string', 'stringWithLength', array('', 'abc', '1234567890')),
        );
    }

    public function providerInstances()
    {
        return array_map(function($values) {
            $class = '\\Properties\\'.$values[1].'Root';
            return array(new $class());
        }, $this->providerPrimitiveValues());
    }


    /**
     * @dataProvider providerPrimitiveValues
     */
    public function testPrimitiveProperty($phpType, $dslType, array $values)
    {
        $this->genericTestProperty($phpType, $dslType, $values, 'assertSame');
    }

    /**
     * @dataProvider providerComplexValues
     */
    public function testComplexProperty($phpType, $dslType, array $values)
    {
        $this->genericTestProperty($phpType, $dslType, $values, 'assertEquals');
    }

    /**
     * @dataProvider providerObjectValues
     */
    public function testObjectProperty($phpType, $dslType, array $values)
    {
        $this->genericTestProperty($phpType, $dslType, $values, 'assertEquals');
    }

    private function genericTestProperty($phpType, $dslType, array $values, $assertion)
    {
        $valuesWithNull = $values;
        $valuesWithNull[] = null;

        $class = '\\Properties\\'.$dslType.'Root';
        $obj = new $class();
        foreach($values as $value) {
            $obj->prop = $value;
            $this->$assertion($value, $obj->prop);
            $obj->null_prop = $value;
            $this->$assertion($value, $obj->null_prop);
        }

        // simple property, non-null
        $obj->null_prop = null;
        $this->$assertion(null, $obj->null_prop);


        // array property, non-null
        $obj->prop_arr = $values;
        $this->$assertion($values, $obj->prop_arr);

        $obj->prop_arr = array();
        $this->$assertion(array(), $obj->prop_arr);


        // array, nullable
        $obj->null_prop_arr = $values;
        $this->$assertion($values, $obj->null_prop_arr);

        $obj->null_prop_arr = array();
        $this->$assertion(array(), $obj->null_prop_arr);

        $obj->null_prop_arr = null;
        $this->$assertion(null, $obj->null_prop_arr);


        // array, non-null, can have nulls
        $obj->prop_arr_with_nulls = $valuesWithNull;
        $this->$assertion($valuesWithNull, $obj->prop_arr_with_nulls);

        $obj->prop_arr_with_nulls = array();
        $this->$assertion(array(), $obj->prop_arr_with_nulls);


        // array, nullable, can have nulls
        $obj->null_prop_arr_with_nulls = $valuesWithNull;
        $this->$assertion($valuesWithNull, $obj->null_prop_arr_with_nulls);
//        echo "\n".$obj->toJson()."\n\n";
        $this->assertEquals($obj, $obj::fromJson($obj->toJson()));

        $obj->null_prop_arr_with_nulls = array();
        $this->$assertion(array(), $obj->null_prop_arr_with_nulls);

        $obj->null_prop_arr_with_nulls = null;
        $this->$assertion(null, $obj->null_prop_arr_with_nulls);
    }


    /**
     * @dataProvider providerInstances
     * @expectedException InvalidArgumentException
     */
    public function testNonNullSimplePropertySetToNull($object)
    {
        $object->prop = null;
    }

    /**
     * @dataProvider providerInstances
     * @expectedException InvalidArgumentException
     */
    public function testNonNullCollectionPropertySetToArrayWithNulls($object)
    {
        $object->prop_arr = array(null);
    }

    /**
     * @dataProvider providerInstances
     * @expectedException InvalidArgumentException
     */
    public function testNonNullCollectionPropertySetToNull($object)
    {
        $object->prop_arr = null;
    }

    /**
     * @dataProvider providerInstances
     * @expectedException InvalidArgumentException
     */
    public function testNullableCollectionPropertySetToArrayWithNulls($object)
    {
        $object->null_prop_arr = array(null);
    }

    /**
     * @dataProvider providerInstances
     * @expectedException InvalidArgumentException
     */
    public function testNonNullCollectionSetToNull($object)
    {
        $object->prop_arr_with_nulls = null;
    }
}
