<?php
namespace NGS;

abstract class Enum
{
    //
    protected static function getStaticClass()
    {
        return __CLASS__;
    }

    // gets actual derived class (late static binding)
    protected static function getClass()
    {
        return static::getStaticClass();
    }

    public static function isValid($label)
    {
        return defined(static::getClass().'::'.$label);
    }

    /**
     * Get all enum values, overriden in generated class
     *
     * @return array
     */
    public static function getValues()
    {
        return array();
    }

    /**
     * Returns enum value from string
     *
     * @param type $value
     * @return type
     * @throws \InvalidArgumentException
     */
    public static function from($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Enum value must be of type string. Invalid value was: "'.\NGS\Utils::getType($value).'"');
        }
        if (!defined(static::getClass().'::'.$value)) {
            throw new \InvalidArgumentException('Invalid value for Enum: "'.$value.'"');
        }
        return $value;
    }

    /**
     * Returns array of enum values from string
     *
     * @param type $value
     * @return type
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $values, $allowNull = true)
    {
        foreach ($values as $val) {
            if ($allowNull && $val === null) {
                continue;
            }
            if (!is_string($val)) {
                throw new \InvalidArgumentException('Enum value must be of type string. Invalid value was: "'.\NGS\Utils::getType($val).'"');
            }
            if (!defined(static::getClass().'::'.$val)) {
               throw new \InvalidArgumentException('Invalid value for Enum: "'.$val.'"');
            }
        }
        return $values;
    }
}
