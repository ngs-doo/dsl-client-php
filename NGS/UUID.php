<?php
namespace NGS;

require_once(__DIR__.'/Utils.php');

use NGS\Utils;

/**
 * UUID implementation
 */
class UUID
{
    /**
     * @var string
     */
    private $value;

    /**
     * Constructs new UUID from UUID string, existing instance, or by generating
     * version 4 UUID
     * @param null|string|\NGS\UUID
     * @throws \InvalidArgumentException
     */
    public function __construct($value = null)
    {
        if (null === $value) {
            $this->value = self::_v4();
        }
        elseif (is_string($value)) {
            if (!self::isValid($value)) {
                throw new \InvalidArgumentException('UUID could not be constructed from invalid value: "'.$value.'"');
            }
            $this->value = $value;
        }
        elseif ($value instanceof \NGS\UUID) {
            $this->value = $value->value;
        }
        else {
            throw new \InvalidArgumentException('UUID could not be constructed from type "'.gettype($value).'"');
        }
    }

    /**
     * Creates array of UUIDs from array by constructing UUID instance from each element of array as __construct
     * argument.
     * arguments
     * @param array $items
     * @return array Array of UUID instances
     * @throws \InvalidArgumentException
     */
    public static function toArray(array $items, $allowNullValues=false)
    {
        $results = array();
        try {
            foreach ($items as $key => $val) {
                if ($allowNullValues && $val===null) {
                    $results[] = null;
                } elseif ($val === null) {
                    throw new \InvalidArgumentException('Null value found in provided array');
                } elseif(!$val instanceof \NGS\UUID) {
                    $results[] = new \NGS\UUID($val);
                } else {
                    $results[] = $val;
                }
            }
        }
        catch(\Exception $e) {
            throw new \InvalidArgumentException('Element at index '.$key.' could not be converted to UUID!', 42, $e);
        }
        return $results;
    }

    /**
     * Allows accessing UUID string value as property, e.g. $instance->value
     * @param string $name
     * @return string
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        if($name==='value') {
            return $this->value;
        }
        else {
            throw new \InvalidArgumentException('UUID: Cannot get undefined property "'.$name.'"');
        }
    }

    /**
     * Constructs new UUID instance with generated version 4 UUID (random)
     * @return \NGS\UUID
     */
    public static function v4()
    {
        return new UUID(self::_v4());
    }

    /**
     * Gets UUID string value
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Validates UUID string
     * @param string $uuid
     * @return bool
     */
    public static function isValid($uuidString)
    {
        if (!is_string($uuidString)) {
            throw new \InvalidArgumentException('UUID value was not a string, type was: '.\NGS\Utils::gettype($uuidString));
        }
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuidString) === 1;
    }

    /**
     * Generate v4 UUID
     * Version 4 UUIDs are pseudo-random.
     * The form is following: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     * where x is any hexadecimal digit and y is one of 8, 9, A, or B
     *
     * @return string
     */
    private static function _v4()
    {
        return sprintf('%04x%04x-%04x-4%03x-%04x-%06x%06x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xfff),
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffffff), mt_rand(0, 0xffffff)
        );
    }
}
