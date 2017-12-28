<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher;

use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\MessageBusEvent;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\MessageBusEventFactory;
use Webit\MessageBus\Message;

class Event4Factory implements MessageBusEventFactory
{
    public function create(Message $message): MessageBusEvent
    {
        return new MessageBusEvent($message->type(), new Event4(new Event1($message->content())));
    }
}
