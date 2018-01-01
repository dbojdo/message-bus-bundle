<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\Publisher;

use Doctrine\Common\Cache\Cache;
use Webit\MessageBus\Message;
use Webit\MessageBus\Publisher;

class CommandPublisher implements Publisher
{
    /** @var Cache */
    private $cache;

    /** @var int */
    public static $counter = 1;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Message $message
     * @throws MessagePublicationException
     */
    public function publish(Message $message)
    {
        $this->cache->save('message_'.self::$counter++, $message);
    }
}
