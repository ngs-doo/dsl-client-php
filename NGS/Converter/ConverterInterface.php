<?php
namespace NGS\Converter;

interface ConverterInterface
{
    public static function toJson($object = null);

    /**
     * @param $json string JSON serialized data
     * @return mixed
     */
    public static function fromJson($json);
}
