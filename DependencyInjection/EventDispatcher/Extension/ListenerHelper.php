<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Extension;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Listener\EventConsumingListener;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Listener\Message\ByEventNameMessageFromEventFactory;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Listener\Message\Content\JmsEventSerialiser;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Listener\Message\Content\PhpSerializeEventSerialiser;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Listener\Message\FallingBackMessageFromEventFactory;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Listener\Message\GenericMessageFromEventFactory;
use Webit\MessageBus\Publisher;
use Webit\MessageBus\PublishingConsumer;
use Webit\MessageBus\VoidConsumer;

final class ListenerHelper
{
    /** @var ContainerBuilder */
    private $container;

    /**
     * PublisherHelper constructor.
     * @param $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public static function byEventNameMessageFromEventFactoryName($listenerName): string
    {
        return sprintf('webit_message_bus.event_dispatcher.listener.%s.by_event_name_message_from_event_factory', $listenerName);
    }

    public function createListener($listenerName, $listenerConfig): Definition
    {
        switch (true) {
            case isset($listenerConfig['consumer']):
                $consumer = new Reference($listenerConfig['consumer']);
                break;
            case isset($listenerConfig['forward_to']):
                $consumer = new Definition(PublishingConsumer::class, [$this->publisherServiceDefinition($listenerConfig['forward_to'])]);
                break;
            default:
                $consumer = new Definition(VoidConsumer::class);
        }

        $supportedEvents = [];
        $fallbackFactory = null;
        $byEventNameMessageFactories = [];

        foreach ($listenerConfig['message_factories'] as $messageFactoryName => $messageFactory) {
            $messageFactoryService = sprintf('webit_message_bus.event_dispatcher.listener.%s.%s.message_from_event_factory', $listenerName, $messageFactoryName);
            switch (true) {
                case isset($messageFactory['jms']):
                    $messageFactoryDefinition = $this->createJmsMessageFactory($messageFactory['jms']);
                    break;
                case isset($messageFactory['php']):
                    $messageFactoryDefinition = $this->createPhpMessageFactory($messageFactory['php']);
                    break;
                case isset($messageFactory['service']):
                    $messageFactoryDefinition = new Reference($messageFactory['service']);
                    $messageFactoryService = $messageFactory['service'];
                    break;
                default:
                    throw new InvalidConfigurationException('Unsupported message factory type.');
            }

            if ($messageFactoryDefinition instanceof Definition) {
                $messageFactoryDefinition->setPrivate(true);
                $messageFactoryDefinition->setLazy(true);
            }

            $supportedEvents = array_merge($supportedEvents, $messageFactory['supports']);
            foreach ($messageFactory['supports'] as $eventName) {
                $byEventNameMessageFactories[$eventName] = new Reference($messageFactoryService);
                $supportedEvents[] = $eventName;
            }

            if ($messageFactory['fallback']) {
                $fallbackFactory = new Reference($messageFactoryService);
            }

            if ($messageFactoryDefinition instanceof Definition) {
                $this->container->setDefinition($messageFactoryService, $messageFactoryDefinition);
            }
        }

        $byEventNameMessageFactory = new Definition(
            ByEventNameMessageFromEventFactory::class,
            [$byEventNameMessageFactories]
        );

        $byTypeMessageFactoryService = self::byEventNameMessageFromEventFactoryName($listenerName);
        $this->container->setDefinition($byTypeMessageFactoryService, $byEventNameMessageFactory);

        $factory = new Reference($byTypeMessageFactoryService);
        if ($fallbackFactory) {
            $factory = new Definition(
                FallingBackMessageFromEventFactory::class,
                [
                    $factory,
                    $fallbackFactory
                ]
            );
        }

        $listener = new Definition(
            EventConsumingListener::class, [
                $consumer,
                $factory
            ]
        );

        foreach (array_unique($supportedEvents) as $event) {
            $listener->addTag($listenerConfig['dispatcher']['listener_tag'], ['event' => $event, 'method' => 'onEvent']);
        }

        return $listener;
    }

    private function createJmsMessageFactory(array $config): Definition
    {
        return new Definition(
            GenericMessageFromEventFactory::class,
            [
                new Definition(
                    JmsEventSerialiser::class,
                    [
                        new Reference($config['serializer']),
                        $this->createContentProvider($config['content_provider'])
                    ]
                ),
                $this->createMessageTypeResolver($config['message_type_resolver'])
            ]
        );
    }

    private function createPhpMessageFactory(array $config): Definition
    {
        return new Definition(
            GenericMessageFromEventFactory::class,
            [
                new Definition(
                    PhpSerializeEventSerialiser::class,
                    [
                        $this->createContentProvider($config['content_provider'])
                    ]
                ),
                $this->createMessageTypeResolver($config['message_type_resolver'])
            ]
        );
    }

    private function createContentProvider(array $config): Reference
    {
        switch($config['type']) {
            case 'event_only':
                return new Reference(
                    'webit_message_bus.event_dispatcher.listener.message_factory.content_provider.event_only'
                );
            case 'custom':
                return new Reference($config['service']);
        }

        throw new InvalidConfigurationException(
            sprintf('Unsupported message type resolver type "%s"', $config['type'])
        );
    }

    private function createMessageTypeResolver($config): Reference
    {
        switch($config['type']) {
            case 'from_event_name':
                return new Reference(
                    'webit_message_bus.event_dispatcher.listener.message_factory.message_type_resolver.from_event_name'
                );
            case 'custom':
                return new Reference($config['service']);
        }

        throw new InvalidConfigurationException(
            sprintf('Unsupported message type resolver type "%s"', $config['type'])
        );
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
