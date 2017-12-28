<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;

class Event4 extends Event
{
    /** @var Event1 */
    private $innerEvent;

    public function __construct(Event1 $innerEvent)
    {
        $this->innerEvent = $innerEvent;
    }

    public function innerEvent(): Event1
    {
        return $this->innerEvent;
    }
}
