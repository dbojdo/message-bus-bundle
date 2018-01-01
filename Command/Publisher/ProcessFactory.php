<?php

namespace Webit\MessageBusBundle\Command\Publisher;

use Symfony\Component\Process\Process;
use Webit\MessageBus\Message;

class ProcessFactory
{
    /** @var string */
    private $binaryPath;

    /** @var string */
    private $environment;

    /** @var array */
    private $environmentVars = [];

    public function __construct(
        string $binaryPath,
        string $environment = 'prod',
        array $environmentVars = []
    ) {
        $this->binaryPath = $binaryPath;
        $this->environment = $environment;
        $this->environmentVars = $environmentVars;
    }

    public function create(Message $message, string $publisherName): Process
    {
        $command = sprintf(
            "%s 'webit_message_bus:publish' %s %s %s --env=%s -q",
            escapeshellarg($this->binaryPath),
            escapeshellarg($publisherName),
            escapeshellarg($message->type()),
            escapeshellarg($message->content()),
            escapeshellarg($this->environment)
        );

        $p = new Process($command, dirname($this->binaryPath), $this->environmentVars);

        return $p;
    }
}
