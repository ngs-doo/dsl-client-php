<?php
namespace NGS\Patterns;

/**
 * Domain object uniquely represented by its URI.
 * Entity and snowflake are example of domain objects which are
 * identified by it's identity, instead of values.
 * While entity does not implement Identifiable, aggregate root does.
 */
abstract class Identifiable extends Searchable implements IIdentifiable
{
}
