<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher;

use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\MessageBusEvent;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\MessageBusEventFactory;
use Webit\MessageBus\Message;

class Event1Factory implements MessageBusEventFactory
{
    /**
     * @inheritdoc
     */
    public function create(Message $message): MessageBusEvent
    {
        $data = @json_decode($message->content(), true);
        return new MessageBusEvent($message->type(), new Event1($data['x']));
    }
}
