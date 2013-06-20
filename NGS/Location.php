<?php
namespace NGS;

use NGS\Converter\PrimitiveConverter;

class Location
{
    protected $x;
    protected $y;

    public function __construct($x=0, $y=0)
    {
        if (is_array($x)) {
            $this->setX($x['X']);
            $this->setY($x['Y']);
        }
        elseif ($x instanceof Location) {
            $this->setX($x->x);
            $this->setY($x->y);
        }
        elseif ($x instanceof Point) {
            $this->setX($x->x);
            $this->setY($x->y);
        }
        else {
            $this->setX($x);
            $this->setY($y);
        }
    }

    public function toArray()
    {
        return array(
            'X' => $this->x,
            'Y' => $this->y
        );
    }

    public function __get($name)
    {
        if ($name==='x') {
            return $this->x;
        }
        if ($name==='y') {
            return $this->y;
        }
        throw new \InvalidArgumentException('Cannot use getter on invalid property '.$name.' in NGS\\Location');
    }

    public function setX($value)
    {
        $this->x = PrimitiveConverter::toFloat($value);
    }

    public function setY($value)
    {
        $this->y = PrimitiveConverter::toFloat($value);
    }

    public function __set($name, $value)
    {
        if ($name==='x') {
            $this->setX($value);
        }
        if ($name==='y') {
            $this->setY($value);
        }
        throw new \InvalidArgumentException('Cannot use setter on invalid property '.$name.' in NGS\\Location');
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
