<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher;

use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\MessageBusEvent;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\MessageBusEventFactory;
use Webit\MessageBus\Message;

class Event3Factory implements MessageBusEventFactory
{
    public function create(Message $message): MessageBusEvent
    {
        return new MessageBusEvent($message->type(), new Event3($message->content()));
    }
}
