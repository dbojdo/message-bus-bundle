<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher;

use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Listener\Message\Exception\UnsupportedEventException;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Listener\Message\MessageFromEventFactory;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\MessageBusEvent;
use Webit\MessageBus\Message;

class Event3ToMessage implements MessageFromEventFactory
{

    public function create(MessageBusEvent $event)
    {
        $symfonyEvent = $event->event();
        if ($symfonyEvent instanceof Event3) {
            return new Message($event->name(), $symfonyEvent->x());
        }

        throw new UnsupportedEventException('Symfony event must be instance of Event3');
    }
}
