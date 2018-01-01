<?php

namespace Webit\MessageBusBundle\DependencyInjection\Command\Extension;

use Symfony\Component\DependencyInjection\Definition;
use Webit\MessageBusBundle\Command\Publisher\AsyncPublishCommandPublisher;
use Webit\MessageBusBundle\Command\Publisher\ProcessFactory;
use Webit\MessageBusBundle\Command\Publisher\ProcessLauncher;
use Webit\MessageBusBundle\Command\Publisher\PublishCommandPublisher;

class CommandExtensionHelper
{
    public function createPublisher(string $publisherName, array $publisherConfig): Definition
    {
        $processFactory = $this->createProcessFactory($publisherConfig['process_factory']);
        if ($publisherConfig['async']['enabled']) {
            return new Definition(
                AsyncPublishCommandPublisher::class,
                [
                    $processFactory,
                    new Definition(
                        ProcessLauncher::class,
                        [
                            $publisherConfig['async']['max_processes']
                        ]
                    ),
                    $publisherConfig['forward_to']
                ]
            );
        }

        return new Definition(
            PublishCommandPublisher::class,
            [
                $processFactory,
                $publisherConfig['forward_to']
            ]
        );
    }

    private function createProcessFactory(array $factoryConfig): Definition
    {
        return new Definition(ProcessFactory::class, [
            $factoryConfig['binary_path'],
            $factoryConfig['environment'],
            $factoryConfig['env_vars']
        ]);
    }
}