<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Webit\MessageBusBundle\DependencyInjection\Amqp\PublisherTag;

final class AmqpPublisherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $publishersMap = [];
        foreach ($container->findTaggedServiceIds(PublisherTag::name()) as $serviceId => $tags) {
            foreach ($this->registerPublisher($serviceId, $tags) as $publisherName) {
                $publishersMap[$publisherName] = $serviceId;
            }
        }

        $publisherRegistry = $container->findDefinition('webit_message_bus.amqp.registry.container_aware');
        $publisherRegistry->replaceArgument(1, $publishersMap);
    }

    private function registerPublisher(string $serviceId, array $tags)
    {
        $names = [];
        foreach ($tags as $tag) {
            $tag = PublisherTag::fromArray($tag, $serviceId);
            $names[] = $tag->publisher();
        }

        return $names;
    }
}
