<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Compiler;

use Behat\Behat\HelperContainer\Exception\ServiceNotFoundException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Extension\PublisherHelper;

final class EventDispatcherPublisherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $factoriesByPublisher = [];
        foreach ($container->findTaggedServiceIds(EventFromMessageFactoryTag::name()) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $tag = EventFromMessageFactoryTag::fromArray($tag, $serviceId);
                $factoriesByPublisher[$tag->publisher()][$tag->event()] = new Reference($serviceId);
            }
        }

        foreach ($factoriesByPublisher as $publisherName => $factories) {
            try {
                $byTypeFactory = $container->findDefinition(
                    PublisherHelper::byTypeEventFromMessageFactoryName($publisherName)
                );
            } catch (ServiceNotFoundException $e) {
                throw new InvalidConfigurationException(
                    sprintf('Could not find "by_type_event_form_message_factory" for the publisher "%s". Are you sure it is configured?', $publisherName),
                    0,
                    $e
                );
            }

            $currentFactories = $byTypeFactory->getArgument(0);
            $byTypeFactory->replaceArgument(0, array_replace($factories, $currentFactories));
        }
    }
}
