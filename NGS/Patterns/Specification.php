<?php
namespace NGS\Patterns;

require_once(__DIR__.'/IDomainObject.php');

/**
 * Search predicate which can be used to filter domain objects from the remote server.
 *
 * Specification is defined in DSL with keyword {@code specification}
 * and a predicate.
 * Server can convert specification to SQL query on the fly or call
 * database function created at compile time. Other optimization techniques
 * can be used too.
 *
 * DSL example:
 * <blockquote><pre>
 * module Todo {
 *   aggregate Task {
 *     timestamp createdOn;
 *     specification findBetween
 *     'it => it.createdOn >= after && it.createdOn <= before' {
 *       date after;
 *       date before;
 *     }
 *   }
 * }
 * </pre></blockquote>
 *
 */
abstract class Specification implements IDomainObject
{
}
