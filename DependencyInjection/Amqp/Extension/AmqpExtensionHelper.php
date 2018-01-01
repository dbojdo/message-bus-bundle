<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Channel\NewChannelConnectionAwareChannelFactory;
use Webit\MessageBus\Infrastructure\Amqp\Connection\ConnectionParams;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Pool\ConnectionPool;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Pool\ConnectionPoolBuilder;
use Webit\MessageBus\Infrastructure\Amqp\Listener\AmqpConsumerBuilder;
use Webit\MessageBus\Infrastructure\Amqp\Listener\AmqpMessageConsumer;
use Webit\MessageBus\Infrastructure\Amqp\Listener\SimpleAmqpListener;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\AmqpPublisher;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\ExchangePublicationTarget;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\QueuePublicationTarget;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\Routing\FromMessageTypeRoutingKeyResolver;
use Webit\MessageBus\Publisher;
use Webit\MessageBus\Consumer\PublishingConsumer;
use Webit\MessageBusBundle\DependencyInjection\Amqp\ConnectionPoolTag;
use Webit\MessageBusBundle\DependencyInjection\Amqp\ListenerTag;
use Webit\MessageBusBundle\DependencyInjection\Amqp\PublisherTag;

final class AmqpExtensionHelper
{
    /** @var ContainerBuilder */
    private $container;

    /** @var XmlFileLoader */
    private $loader;

    public function __construct(ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->container = $container;
        $this->loader = $loader;
    }

    public static function hasAmqp(): bool
    {
        return class_exists(AmqpPublisher::class);
    }

    public function configureAmqp(array $config)
    {
        $this->loader->load('amqp.xml');

        $this->configureConnectionPools($config['connection_pools']);

    }

    private function configureConnectionPools(array $config)
    {
        foreach ($config as $poolName => $connections) {
            $this->container->setDefinition(
                sprintf('webit_message_bus.amqp.connection_pool.%s', $poolName),
                $this->createAmqpConnectionPool($poolName, $connections)
            );

        }
    }

    private function createAmqpConnectionPool(string $poolName, array $connections): Definition
    {
        $poolFactory = new Definition(ConnectionPoolBuilder::class);
        $poolFactory->setFactory([ConnectionPoolBuilder::class, 'create']);
        $poolFactory->addMethodCall('setLogger', [new Reference('logger')]);

        $pool = new Definition(ConnectionPool::class);
        $pool->setFactory([$poolFactory, 'build']);

        foreach ($connections as $connectionName => $connection) {
            $poolFactory->addMethodCall(
                'registerConnection',
                [
                    new Definition(ConnectionParams::class, [
                        $connection['host'],
                        $connection['port'],
                        $connection['username'],
                        $connection['password'],
                        $connection['vhost']
                    ]),
                    $connectionName
                ]
            );
        }

        $tag = new ConnectionPoolTag($poolName);
        $pool->addTag(ConnectionPoolTag::name(), $tag->options());

        return $pool;
    }

    public function createPublisher($publisherName, array $publisherConfig): Definition
    {
        $publisher = new Definition(
            AmqpPublisher::class,
            [
                $this->createAmqpPublicationTarget($publisherConfig['target'])
            ]
        );

        $tag = new PublisherTag($publisherName);
        $publisher->addTag(PublisherTag::name(), $tag->options());

        return $publisher;
    }

    private function createAmqpPublicationTarget(array $config): Definition
    {
        if (isset($config['exchange'])) {
            return new Definition(
                ExchangePublicationTarget::class, [
                    $this->createAmqpChannelFactory($config['pool']),
                    new Definition(FromMessageTypeRoutingKeyResolver::class),
                    $config['exchange']['name']
                ]
            );
        }

        if (isset($config['queue'])) {
            return new Definition(
                QueuePublicationTarget::class, [
                    $this->createAmqpChannelFactory($config['pool']),
                    $config['queue']
                ]
            );
        }

        throw new \InvalidArgumentException(
            'Non of recognised publication target types ["exchange", "queue"] could be found in given config.'
        );
    }

    private function createAmqpChannelFactory(string $pool): Definition
    {
        $pool = new Definition(ConnectionPool::class, [$pool]);
        $pool->setFactory(
            [new Reference('webit_message_bus.amqp.connection_pools'), 'connectionPool']
        );

        return new Definition(
            NewChannelConnectionAwareChannelFactory::class, [
                $pool
            ]
        );
    }

    public function createListener(string $listenerName, array $config): Definition
    {
        $amqpConsumerBuilder = new Definition(AmqpConsumerBuilder::class);
        $amqpConsumerBuilder->addMethodCall('setLogger', [new Reference('logger')]);

        $consumer = null;

        if (isset($config['consumer'])) {
            $consumer = new Reference($config['consumer']);
        }

        if (isset($config['forward_to'])) {
            $consumer = new Definition(PublishingConsumer::class, [$this->publisherServiceDefinition($config['forward_to'])]);
        }

        if ($consumer) {
            $amqpConsumerBuilder->addMethodCall('setConsumer', [$consumer]);
        }

        if (isset($config['message_factory'])) {
            $amqpConsumerBuilder->addMethodCall('setMessageFactory', [new Reference($config['message_factory'])]);
        }

        $amqpMessageConsumer = new Definition(AmqpMessageConsumer::class);
        $amqpMessageConsumer->setFactory([$amqpConsumerBuilder, 'build']);

        $listener = new Definition(SimpleAmqpListener::class, [
            $this->createAmqpChannelFactory($config['pool']),
            $amqpMessageConsumer,
            $config['queue'],
            $config['qos']['prefetch_count'] ?? null
        ]);

        $tag = new ListenerTag($listenerName);
        $listener->addTag(ListenerTag::name(), $tag->options());

        return $listener;
    }

    private function publisherServiceDefinition($publisherName): Definition
    {
        $definition = new Definition(
            Publisher::class,
            [$publisherName]
        );
        $definition->setFactory([new Reference('webit_message_bus.publisher_registry'), 'getPublisher']);
        return $definition;
    }
}
