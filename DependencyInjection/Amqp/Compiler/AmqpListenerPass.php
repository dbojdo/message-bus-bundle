<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Webit\MessageBusBundle\DependencyInjection\Amqp\ListenerTag;

final class AmqpListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $listenersMap = [];
        foreach ($container->findTaggedServiceIds(ListenerTag::name()) as $serviceId => $tags) {
            foreach ($this->registerListener($serviceId, $tags) as $listenerName) {
                $listenersMap[$listenerName] = $serviceId;
            }
        }

        $listenerRegistry = $container->findDefinition('webit_message_bus.amqp.registry.container_aware');
        $listenerRegistry->replaceArgument(2, $listenersMap);
    }

    private function registerListener(string $serviceId, array $tags)
    {
        $names = [];
        foreach ($tags as $tag) {
            $tag = ListenerTag::fromArray($tag, $serviceId);
            $names[] = $tag->listener();
        }

        return $names;
    }
}