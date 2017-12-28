<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake;

use Webit\MessageBus\Consumer;
use Webit\MessageBus\Exception\MessageConsumptionException;
use Webit\MessageBus\Message;

class SpyableConsumer implements Consumer
{
    /** @var Consumer */
    private $innerConsumer;

    public function __construct(Consumer $innerConsumer)
    {
        $this->innerConsumer = $innerConsumer;
    }

    /**
     * @inheritdoc
     */
    public function consume(Message $message)
    {
        $this->innerConsumer->consume($message);
    }
}
