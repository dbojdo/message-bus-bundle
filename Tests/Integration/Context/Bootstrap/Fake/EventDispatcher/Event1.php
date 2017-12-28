<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher;

use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Annotation as JMS;

class Event1 extends AbstractEvent
{
    /**
     * @JMS\HandlerCallback("json", direction = "serialization")
     */
    public function jsonSerialize(JsonSerializationVisitor $visitor, $type, $context)
    {
        $visitor->visitArray(['x' => $this->x()], [], $context);
    }
}
