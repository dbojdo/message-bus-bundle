<?php

namespace Webit\MessageBusBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PublisherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $publishersMap = [];
        foreach ($container->findTaggedServiceIds(PublisherTag::name()) as $serviceId => $tags) {
            $container->findDefinition($serviceId)->setPublic(true);
            foreach ($this->registerPublisher($serviceId, $tags) as $publisherName) {
                $publishersMap[$publisherName] = $serviceId;
            }
        }

        $publisherRegistry = $container->findDefinition('webit_message_bus.publisher_registry.container_aware');
        $publisherRegistry->replaceArgument(0, $publishersMap);
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
