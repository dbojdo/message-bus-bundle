<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\Publisher;

use Psr\Log\LoggerInterface;
use Webit\MessageBus\Message;
use Webit\MessageBus\Publisher;

class SleepingCommandPublisher implements Publisher
{
    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    private $sleep;

    public function __construct(LoggerInterface $logger, int $sleep = 1)
    {
        $this->sleep = $sleep * 1000000;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function publish(Message $message)
    {
        $totalSleep = 0;

        $this->logger->info(sprintf('[%s:%s] Starting.', $message->type(), $message->content()));
        while ($totalSleep <= $this->sleep) {
            $currentSleep = mt_rand(1, $this->sleep);
            $this->logger->info(sprintf('[%s:%s] Sleeping for %s s', $message->type(), $message->content(), $currentSleep / 1000000));
            usleep($currentSleep);
            $totalSleep += $currentSleep;
        }
        $this->logger->info(sprintf('[%s:%s] Done.', $message->type(), $message->content()));
    }
}
