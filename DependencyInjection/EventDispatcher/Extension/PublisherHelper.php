<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Extension;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\ByMessageTypeMessageBusEventFactory;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\FallingBackMessageBusEventFactory;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\GenericMessageBusEventFactory;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\Symfony\ByMessageTypeJMSSerializerTypeMap;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\Symfony\JMSSerializerSymfonyEventFactory;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\Event\Symfony\PhpUnserializeEventFactory;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\EventDispatcherPublisher;

final class PublisherHelper
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

    public function createPublisher(string $publisherName, array $publisherConfig): Definition
    {
        $publisher = new Definition(
            EventDispatcherPublisher::class,
            [
                new Reference($publisherConfig['dispatcher']),
                $this->createEventFromMessageFactory($publisherConfig['event_factories'], $publisherName)
            ]
        );

        return $publisher;
    }

    public static function byTypeEventFromMessageFactoryName($publisherName): string
    {
        return sprintf('webit_message_bus.event_dispatcher.publisher.%s.by_type_event_from_message_factory', $publisherName);
    }

    private function createEventFromMessageFactory(array $eventFactories, $publisherName)
    {
        $fallbackFactory = null;
        $byTypeFactories = [];

        foreach ($eventFactories as $factoryName => $eventFactory) {
            $factoryService = sprintf('webit_message_bus.event_dispatcher.publisher.%s.%s.event_factory', $publisherName, $factoryName);

            switch(true) {
                case isset($eventFactory['php']):
                    $eventFactoryDefinition = $this->createPhpEventFactory($eventFactory['php']);
                    break;
                case isset($eventFactory['jms']):
                    $eventFactoryDefinition = $this->createJmsEventFactory($eventFactory['jms']);
                    break;
                case isset($eventFactory['service']):
                    $eventFactoryDefinition = new Reference($eventFactory['service']);
                    $factoryService = $eventFactory['service'];
                    break;
                default:
                    throw new InvalidConfigurationException('Unsupported event factory.');
            }

            if ($eventFactoryDefinition instanceof Definition) {
                $eventFactoryDefinition->setLazy(true);
                $eventFactoryDefinition->setPrivate(true);

                $this->container->setDefinition(
                    $factoryService,
                    $eventFactoryDefinition
                );
            }

            foreach ($eventFactory['supports'] as $messageType) {
                $byTypeFactories[$messageType] = $eventFactoryDefinition;
            }

            if ($eventFactory['fallback']) {
                $fallbackFactory = new Reference($factoryService);
            }
        }

        $byTypeFactory = new Definition(ByMessageTypeMessageBusEventFactory::class, [$byTypeFactories]);
        $byTypeFactory->setPrivate(true);

        $this->container->setDefinition(
            $byTypeFactoryService = self::byTypeEventFromMessageFactoryName($publisherName),
            $byTypeFactory
        );

        if ($fallbackFactory) {
            return new Definition(FallingBackMessageBusEventFactory::class, [
                new Reference($byTypeFactoryService),
                $fallbackFactory
            ]);
        }

        return new Reference($byTypeFactoryService);
    }

    /**
     * @param array $config
     * @return Definition
     */
    private function createPhpEventFactory(array $config)
    {
        return new Definition(GenericMessageBusEventFactory::class, [
            new Definition(PhpUnserializeEventFactory::class),
            $this->createEventNameResolver($config['event_name_resolver'])
        ]);
    }

    /**
     * @param array $config
     * @return Definition
     */
    private function createJmsEventFactory(array $config)
    {
        $typesMap = [];
        foreach ($config['types_map'] as $jmsType => $messageTypes) {
            foreach ($messageTypes as $messageType) {
                $typesMap[$messageType] = $jmsType;
            }
        }

        return new Definition(GenericMessageBusEventFactory::class, [
            new Definition(JMSSerializerSymfonyEventFactory::class, [
                new Reference($config['serializer']),
                new Definition(ByMessageTypeJMSSerializerTypeMap::class, [$typesMap]),
                $config['format']
            ]),
            $this->createEventNameResolver($config['event_name_resolver'])
        ]);
    }

    private function createEventNameResolver(array $config): Reference
    {
        switch($config['type']) {
            case 'from_message_type':
                return new Reference(
                    'webit_message_bus.event_dispatcher.publisher.event_factory.event_name_resolver.from_message_type'
                );
            case 'custom':
                return new Reference($config['service']);
        }

        throw new InvalidConfigurationException(
            sprintf('Unsupported event name resolver type "%s"', $config['type'])
        );
    }
}