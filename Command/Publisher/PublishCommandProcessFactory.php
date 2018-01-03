<?php

namespace Webit\MessageBusBundle\Command\Publisher;

use Symfony\Component\Process\Process;
use Webit\MessageBus\Infrastructure\Symfony\Process\Launcher\ProcessFactory;
use Webit\MessageBus\Message;

final class PublishCommandProcessFactory implements ProcessFactory
{
    /** @var string */
    private $binaryPath;

    /** @var string */
    private $command;

    /** @var string */
    private $publisherName;

    /** @var string */
    private $environment;

    /** @var array */
    private $environmentVars = [];

    public function __construct(
        string $binaryPath,
        string $command,
        string $publisherName,
        string $environment = 'prod',
        array $environmentVars = []
    ) {
        $this->binaryPath = $binaryPath;
        $this->publisherName = $publisherName;
        $this->environment = $environment;
        $this->environmentVars = $environmentVars;
        $this->command = $command;
    }

    /**
     * @inheritdoc
     */
    public function create(Message $message)
    {
        $command = sprintf(
            "%s '%s' %s %s %s --env=%s -q",
            escapeshellarg($this->binaryPath),
            escapeshellarg($this->command),
            escapeshellarg($this->publisherName),
            escapeshellarg($message->type()),
            escapeshellarg($message->content()),
            escapeshellarg($this->environment)
        );

        return new Process($command, dirname($this->binaryPath), $this->environmentVars);
    }
}
