<?php

namespace Webit\MessageBusBundle\DependencyInjection\Command\Extension;

use Symfony\Component\DependencyInjection\Definition;
use Webit\MessageBus\Infrastructure\Symfony\Process\Launcher\AsynchronousProcessLauncher;
use Webit\MessageBus\Infrastructure\Symfony\Process\Launcher\ParallelProcessManager;
use Webit\MessageBus\Infrastructure\Symfony\Process\Launcher\SynchronousProcessLauncher;
use Webit\MessageBus\Infrastructure\Symfony\Process\ProcessPublisher;
use Webit\MessageBusBundle\Command\Publisher\PublishCommandProcessFactory;

class CommandExtensionHelper
{
    public function createPublisher(string $publisherName, array $publisherConfig): Definition
    {
        $processFactory = $this->createProcessFactory(
            $publisherConfig['process_factory'],
            $publisherConfig['forward_to']
        );

        if ($publisherConfig['async']['enabled']) {
            return new Definition(
                ProcessPublisher::class,
                [
                    new Definition(
                        AsynchronousProcessLauncher::class,
                        [
                            $processFactory,
                            new Definition(
                                ParallelProcessManager::class,
                                [$publisherConfig['async']['max_processes']]
                            )

                        ]
                    )
                ]
            );
        }

        return new Definition(
            ProcessPublisher::class,
            [
                new Definition(
                    SynchronousProcessLauncher::class,
                    [
                        $processFactory
                    ]
                )
            ]
        );
    }

    private function createProcessFactory(array $factoryConfig, string $publisherName): Definition
    {
        return new Definition(PublishCommandProcessFactory::class, [
            $factoryConfig['binary_path'],
            $factoryConfig['command'],
            $publisherName,
            $factoryConfig['environment'],
            $factoryConfig['env_vars']
        ]);
    }
}