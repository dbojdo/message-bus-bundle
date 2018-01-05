<?php

namespace Webit\MessageBusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Webit\MessageBus\Publisher;
use Webit\MessageBus\Publisher\Exception\Handler\ByClassExceptionHandler;
use Webit\MessageBus\Publisher\Exception\Handler\IgnoringExceptionHandler;
use Webit\MessageBus\Publisher\Exception\Handler\LoggingExceptionHandler;
use Webit\MessageBus\Publisher\Exception\Handler\PublishingExceptionHandler;
use Webit\MessageBus\Publisher\Exception\Handler\ThrowingExceptionHandler;
use Webit\MessageBus\Publisher\ExceptionHandlingPublisher;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Extension\AmqpExtensionHelper;
use Webit\MessageBusBundle\DependencyInjection\Command\Extension\CommandExtensionHelper;
use Webit\MessageBusBundle\DependencyInjection\Compiler\PublisherTag;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Extension\EventDispatcherExtensionHelper;

class WebitMessageBusExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $amqpHelper = new AmqpExtensionHelper($container, $loader);
        if (AmqpExtensionHelper::hasAmqp()) {
            $amqpHelper->configureAmqp($config['amqp']);
        }

        $eventDispatcherHelper = new EventDispatcherExtensionHelper($container, $loader);
        if (EventDispatcherExtensionHelper::hasEventDispatcher()) {
            $eventDispatcherHelper->configureEventDispatcher();
        }

        $loader->load('publishers.xml');

        $this->configurePublishers(
            $config['publishers'],
            $container,
            $amqpHelper,
            $eventDispatcherHelper,
            new CommandExtensionHelper()
        );

        $this->configureListeners($config['listeners'], $container, $amqpHelper, $eventDispatcherHelper);
    }

    private function configurePublishers(
        array $config,
        ContainerBuilder $container,
        AmqpExtensionHelper $amqpHelper,
        EventDispatcherExtensionHelper $eventDispatcherHelper,
        CommandExtensionHelper $commandExtensionHelper
    ) {
        foreach ($config as $publisherName => $publisherConfig) {
            switch (true) {
                case isset($publisherConfig['amqp']):
                    $publisherDefinition = $amqpHelper->createPublisher($publisherName, $publisherConfig['amqp']);
                    break;
                case isset($publisherConfig['event_dispatcher']):
                    $publisherDefinition = $eventDispatcherHelper->createPublisher(
                        $publisherName,
                        $publisherConfig['event_dispatcher']
                    );
                    break;
                case isset($publisherConfig['command']):
                    $publisherDefinition = $commandExtensionHelper->createPublisher(
                        $publisherName,
                        $publisherConfig['command']
                    );
                    break;
                default:
                    throw new InvalidConfigurationException(
                        sprintf('Configuration of the "%s" publisher does not provide any known implementation ["amqp", "event_dispatcher"]',
                            $publisherName)
                    );
            }

            if (isset($publisherConfig['on_exception'])) {
                $publisherDefinition = $this->wrapPublisherWithExceptionHandler($publisherDefinition, $publisherConfig['on_exception']);
            }
            $publisherDefinition->setLazy(true);

            $tag = new PublisherTag($publisherName);
            $publisherDefinition->addTag(PublisherTag::name(), $tag->options());

            $container->setDefinition(
                $serviceName = sprintf('webit_message_bus.publisher.%s', $publisherName),
                $publisherDefinition
            );
        }
    }

    private function configureListeners(
        array $config,
        ContainerBuilder $container,
        AmqpExtensionHelper $amqpHelper,
        EventDispatcherExtensionHelper $eventDispatcherHelper
    ) {
        foreach ($config as $listenerName => $listenerConfig) {
            switch (true) {
                case isset($listenerConfig['amqp']):
                    $listenerDefinition = $amqpHelper->createListener($listenerName, $listenerConfig['amqp']);
                    break;
                case isset($listenerConfig['event_dispatcher']):
                    $listenerDefinition = $eventDispatcherHelper->createListener(
                        $listenerName,
                        $listenerConfig['event_dispatcher']
                    );
                    break;
                default:
                    throw new InvalidConfigurationException(
                        sprintf(
                            'Configuration of the "%s" listener does not provide any known implementation ["amqp", "event_dispatcher"]',
                            $listenerName
                        )
                    );
            }

            $container->setDefinition(
                sprintf('webit_message_bus.listener.%s', $listenerName),
                $listenerDefinition
            );
        }
    }

    private function wrapPublisherWithExceptionHandler(Definition $publisherDefinition, array $onException)
    {
        $handler = new Definition(ByClassExceptionHandler::class, [
            $this->createPublisherExceptionHandler($onException['unsupported_message']),
            $this->createPublisherExceptionHandler($onException['default'])
        ]);

        return new Definition(ExceptionHandlingPublisher::class, [
            $publisherDefinition,
            $handler
        ]);
    }

    private function createPublisherExceptionHandler(array $handlerConfig)
    {
        if (isset($handlerConfig['service'])) {
            return new Reference($handlerConfig['service']);
        }

        switch ($handlerConfig['strategy']) {
            case 'ignore':
                $handler = new Definition(IgnoringExceptionHandler::class);
                break;
            case 'throw':
                $handler = new Definition(ThrowingExceptionHandler::class);
                break;
            default:
                throw new InvalidConfigurationException(
                    sprintf('Unsupported exception handling strategy "%s"', $handlerConfig['strategy'])
                );
        }

        if ($handlerConfig['logger']) {
            $handler = new Definition(
                LoggingExceptionHandler::class,
                [
                    new Reference($handlerConfig['logger']),
                    $handler
                ]
            );
        }

        if ($handlerConfig['forward_to']) {
            $publisher = new Definition(Publisher::class, [$handlerConfig['forward_to']]);
            $publisher->setFactory([new Reference('webit_message_bus.publisher_registry'), 'getPublisher']);

            $handler = new Definition(
                PublishingExceptionHandler::class,
                [
                    $publisher,
                    $handler
                ]
            );
        }

        return $handler;
    }
}
