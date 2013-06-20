<?php
namespace NGS;

use NGS\Location;
use NGS\Converter\PrimitiveConverter;

class Point extends Location
{
    public function setX($value)
    {
        $this->x = PrimitiveConverter::toInteger($value);
    }

    public function setY($value)
    {
        $this->y = PrimitiveConverter::toInteger($value);
    }
}
