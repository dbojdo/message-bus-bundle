<?php

namespace Webit\MessageBusBundle\Command\Publisher;

use Webit\MessageBus\Message;
use Webit\MessageBus\Publisher;
use Webit\MessageBusBundle\Command\Publisher\Exception\ProcessPoolIsFullException;

final class AsyncPublishCommandPublisher implements Publisher
{
    /** @var ProcessFactory */
    private $processFactory;

    /** @var ProcessLauncher */
    private $processManager;

    /** @var string */
    private $publisherName;

    public function __construct(ProcessFactory $processFactory, ProcessLauncher $processManager, string $publisherName)
    {
        $this->processFactory = $processFactory;
        $this->processManager = $processManager;
        $this->publisherName = $publisherName;
    }

    /**
     * @inheritdoc
     */
    public function publish(Message $message)
    {
        $process = $this->processFactory->create($message, $this->publisherName);
        do {
            $exception = null;
            try {
                $this->processManager->launch($process);
            } catch (ProcessPoolIsFullException $e) {
                $exception = $e;
                sleep(1);
            }
        } while($exception);
    }
}
