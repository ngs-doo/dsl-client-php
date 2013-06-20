<?php
namespace NGS\Patterns;

require_once(__DIR__.'/../Client/DomainProxy.php');
require_once(__DIR__.'/../Converter/PrimitiveConverter.php');

use NGS\Client\DomainProxy;
use NGS\Converter\PrimitiveConverter;

/**
 * Event which is applied on aggregate root.
 */
abstract class AggregateDomainEvent
{
    /**
     * Applies event to aggregate root object
     *
     * @param \NGS\Patterns\AggregateRoot $value Aggregate instance on which
     * event will be applied
     * @return \NGS\Patterns\AggregateRoot Aggregate with updated values after
     * the event was executed
     * @throws \InvalidArgumentException
     */
    public function submit($value=null)
    {
        if ($value === null) {
            throw new \InvalidArgumentException("argument can't be null. It must be aggregate or it's uri");
        }
        if ($value instanceof AggregateRoot) {
            return  DomainProxy::instance()->submitAggregateEvent($this, $value->getURI());
        }
        return DomainProxy::instance()->submitAggregateEvent($this, PrimitiveConverter::toString($value));
    }
}
