<?php
namespace NGS\Patterns;

require_once(__DIR__.'/../Client/DomainProxy.php');

use NGS\Client\DomainProxy;

/**
 * Domain event
 */
abstract class DomainEvent
{
    /**
     * Submits event
     *
     * @return string Created event URI
     */
    public function submit()
    {
        $eventUri = DomainProxy::instance()->submitEvent($this);
        if (is_string($eventUri)) {
            $this->URI = $eventUri;
        }
        return $eventUri;
    }
}
