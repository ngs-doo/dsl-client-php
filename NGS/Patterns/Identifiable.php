<?php
namespace NGS\Patterns;
use NGS\Converter\ObjectConverter;

/**
 * Domain object uniquely represented by its URI.
 * Entity and snowflake are example of domain objects which are
 * identified by it's identity, instead of values.
 * While entity does not implement Identifiable, aggregate root does.
 */
abstract class Identifiable extends Searchable implements IIdentifiable, \Serializable
{
    public function serialize()
    {
        return $this->toJson();
    }

    public function unserialize($serialized)
    {
        $converter = ObjectConverter::getConverter(get_class($this), ObjectConverter::JSON_TYPE);
        return $converter::fromJson($serialized);
    }
}
