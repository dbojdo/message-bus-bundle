<?php

namespace Webit\MessageBusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
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
}
