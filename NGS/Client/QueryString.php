<?php
namespace NGS\Client;

use NGS\Converter\PrimitiveConverter;

require_once(__DIR__.'/../Converter/PrimitiveConverter.php');

/**
 * Serializes various parameters into URL query string
 *
 * @package NGS\Client
 */
abstract class QueryString
{
    private static function prepareLimit($limit)
    {
        $limit = $limit === null ? null : PrimitiveConverter::toInteger($limit);
        if (!$limit) {
            return null;
        }
        return 'limit='.$limit;
    }

    private static function prepareOffset($offset)
    {
        $offset = $offset === null ? null : PrimitiveConverter::toInteger($offset);
        if (!$offset) {
            return null;
        }
        return 'offset='.$offset;
    }

    /**
     * Serializes cube parameters into a query string
     *
     * @param array $dimensions
     * @param array $facts
     * @param array $order
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function prepareCubeCall(array $dimensions, array $facts, array $order, $limit=null, $offset=null)
    {
        $params = array();

        if ($dimensions) {
            $params[] = 'dimensions='.implode(',', array_map(function($a){ return rawurlencode($a); }, $dimensions));
        }
        if ($facts) {
            $params[] = 'facts='.implode(',', array_map(function($a){ return rawurlencode($a); }, $facts));
        }
        if ($order) {
            array_walk($order, function(&$el, $key) {
                if (is_int($key)) {
                    $el = rawurlencode($el);
                } elseif (is_bool($el)) {
                    $el = ($el ? '' : '-') . rawurlencode($key);
                } else {
                    throw new \InvalidArgumentException('Cube order was not a string array or a string=>bool array');
                }
            });
            $params[] = 'order='.implode(',', $order);
        }
        if ($limit = static::prepareLimit($limit)) {
            $params[] = $limit;
        }
        if ($offset = static::prepareOffset($offset)) {
            $params[] = $offset;
        }

        if (!$params) {
            throw new \InvalidArgumentException('Cube must have at least one argument');
        }
        return implode('&', $params);
    }

    /**
     * Serializes limit, offset and order to URL query string
     *
     * @param $limit string|int|float
     * @param $offset string|int|float
     * @param $order array Array of string=>bool pairs, where key is property_name,
     * and value is true for ascending, and false for descending order
     * <code>
     * $order = array(
     *     'name' => true, // order by ascending name
     *     'surname' => false // order by descending surname)
     * );
     * </code>
     * @return string
     */
    public static function formatLimitAndOffsetAndOrder($limit, $offset, array $order=null)
    {
        $params = array();

        if ($limit = static::prepareLimit($limit)) {
            $params[] = $limit;
        }
        if ($offset = static::prepareOffset($offset)) {
            $params[] = $offset;
        }
        if ($order) {
            array_walk($order, function(&$v, $k) {  $v = $v ? $k : '-'.$k; });
            $params[] = 'order='.implode (',', $order);
        }
        return implode('&', $params);
    }
}
