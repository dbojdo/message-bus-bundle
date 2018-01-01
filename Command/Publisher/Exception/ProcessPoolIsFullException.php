<?php

namespace Webit\MessageBusBundle\Command\Publisher\Exception;

use Symfony\Component\Process\Process;

class ProcessPoolIsFullException extends \RuntimeException
{
    /** @var Process */
    private $process;

    public static function forProcess(Process $process, $code = 0, \Exception $previous = null)
    {
        $exception = new self(
            sprintf('Process pool is full.'),
            $code,
            $previous
        );

        $exception->process = $process;

        return $exception;
    }

    public function process(): Process
    {
        return $this->process;
    }
}