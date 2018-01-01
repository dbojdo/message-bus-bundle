<?php

namespace Webit\MessageBusBundle\Command\Publisher;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Webit\MessageBus\Message;
use Webit\MessageBus\Publisher;

final class PublishCommandPublisher implements Publisher
{
    /** @var ProcessFactory */
    private $processFactory;

    /** @var string */
    private $publisherName;

    public function __construct(ProcessFactory $processFactory, string $publisherName)
    {
        $this->processFactory = $processFactory;
        $this->publisherName = $publisherName;
    }

    /**
     * @inheritdoc
     */
    public function publish(Message $message)
    {
        $process = $this->processFactory->create($message, $this->publisherName);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw Publisher\Exception\CannotPublishMessageException::forMessage($message, 0, $e);
        }
    }
}
