<?php

namespace Webit\MessageBusBundle\Amqp;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Pool\ConnectionPool;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Pool\Registry\ConnectionPoolRegistry;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Pool\Registry\Exception\ConnectionPoolNotFoundException;
use Webit\MessageBus\Infrastructure\Amqp\Listener\AmqpListener;
use Webit\MessageBus\Infrastructure\Amqp\Listener\Registry\Exception\ListeningNotFoundException;
use Webit\MessageBus\Infrastructure\Amqp\Listener\Registry\ListenerRegistry;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\AmqpPublisher;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\Registry\Exception\PublisherNotFoundException;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\Registry\PublisherRegistry;

final class AmqpRegistry implements ConnectionPoolRegistry, PublisherRegistry, ListenerRegistry
{
    use ContainerAwareTrait;

    /** @var string[] */
    private $connectionPoolsMap;

    /** @var string[] */
    private $publishersMap;

    /** @var string[] */
    private $listenersMap;

    public function __construct(array $connectionPoolsMap, array $publishersMap, array $listenersMap)
    {
        $this->connectionPoolsMap = $connectionPoolsMap;
        $this->publishersMap = $publishersMap;
        $this->listenersMap = $listenersMap;
    }

    /**
     * @inheritdoc
     */
    public function connectionPool(string $poolName): ConnectionPool
    {
        try {
            return $this->resolveService($this->connectionPoolsMap, $poolName);
        } catch (\Exception $e) {
            throw ConnectionPoolNotFoundException::fromPoolName($poolName);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerConnectionPool(ConnectionPool $connectionPool, string $poolName)
    {
        $this->throwUnsupportedMethodException(__METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function listener(string $listenerName): AmqpListener
    {
        try {
            return $this->resolveService($this->listenersMap, $listenerName);
        } catch (\Exception $e) {
            throw ListeningNotFoundException::fromListenerName($listenerName);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerListener(AmqpListener $listener, string $listenerName)
    {
        $this->throwUnsupportedMethodException(__METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function publisher(string $publisherName): AmqpPublisher
    {
        try {
            return $this->resolveService($this->publishersMap, $publisherName);
        } catch (\Exception $e) {
            throw PublisherNotFoundException::fromPublisherName($publisherName);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerPublisher(AmqpPublisher $publisher, string $publisherName)
    {
        $this->throwUnsupportedMethodException(__METHOD__);
    }

    /**
     * @param array $map
     * @param string $amqpServiceName
     * @return object|ConnectionPool|AmqpListener|AmqpPublisher
     */
    private function resolveService(array &$map, $amqpServiceName)
    {
        $serviceName = isset($map[$amqpServiceName]) ? $map[$amqpServiceName] : null;

        if ($serviceName && $this->container->has($serviceName)) {
            return $this->container->get($serviceName);
        }

        throw new \OutOfBoundsException(sprintf('Cannot locate service for "%s".', $amqpServiceName));
    }

    private function throwUnsupportedMethodException($method)
    {
        throw new \InvalidArgumentException(
            sprintf('AmqpRegistry::%s is not supported by this registry.', $method)
        );
    }
}