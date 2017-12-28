<?php

namespace Webit\MessageBusBundle\Publisher;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Webit\MessageBus\Exception\UnregisteredPublisherException;
use Webit\MessageBus\Publisher;
use Webit\MessageBus\PublisherRegistry;

final class ContainerAwarePublisherRegistry implements PublisherRegistry
{
    use ContainerAwareTrait;

    /** @var string[] */
    private $publisherMap = [];

    public function __construct(array $publisherMap)
    {
        $this->publisherMap = $publisherMap;
    }

    /**
     * @inheritdoc
     */
    public function getPublisher(string $name): Publisher
    {
        $publisherService = $this->resolvePublisherServiceName($name);

        if ($this->container->has($publisherService)) {
            return $this->container->get($publisherService);
        }

        throw UnregisteredPublisherException::fromPublisherName($name);
    }

    private function resolvePublisherServiceName($name)
    {
        if (isset($this->publisherMap[$name])) {
            return $this->publisherMap[$name];
        }

        throw UnregisteredPublisherException::fromPublisherName($name);
    }
}
