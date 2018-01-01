<?php

namespace Webit\MessageBusBundle\Command\Publisher;

use Symfony\Component\Process\Process;
use Webit\MessageBusBundle\Command\Publisher\Exception\ProcessPoolIsFullException;

class ProcessLauncher
{
    /** @var int */
    private $maxRunningProcesses;

    /** @var Process[] */
    private $processes;

    /**
     * ProcessManager constructor.
     * @param int $maxRunningProcesses
     */
    public function __construct(int $maxRunningProcesses = 5)
    {
        $this->maxRunningProcesses = $maxRunningProcesses;
        $this->processes = [];
    }

    public function launch(Process $process)
    {
        $this->checkRunningProcesses();
        $this->startProcess($process);
    }

    private function checkRunningProcesses()
    {
        $currentProcesses = $this->processes;

        foreach ($currentProcesses as $k => $process) {
            if (!$process->isRunning()) {
                $this->unsetProcess($process);
            }
        }
    }

    private function unsetProcess(Process $process)
    {
        $key = array_search($process, $this->processes, true);
        if ($key !== false) {
            unset($this->processes[$key]);
        }
    }

    private function startProcess(Process $process)
    {
        if (count($this->processes) >= $this->maxRunningProcesses) {
            throw ProcessPoolIsFullException::forProcess($process);
        }

        $this->processes[] = $process;
        $process->start();
    }
}
