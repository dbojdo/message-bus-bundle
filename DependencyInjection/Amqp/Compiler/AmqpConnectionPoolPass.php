<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Webit\MessageBusBundle\DependencyInjection\Amqp\ConnectionPoolTag;

final class AmqpConnectionPoolPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registry = $container->findDefinition('webit_message_bus.amqp.registry.container_aware');
        $poolMap = [];

        foreach ($container->findTaggedServiceIds(ConnectionPoolTag::name()) as $serviceId => $tags) {
            $container->findDefinition($serviceId)->setPublic(true);
            foreach ($this->registerConnectionPool($serviceId, $tags) as $poolName) {
                $poolMap[$poolName] = $serviceId;
            }
        }
        $registry->replaceArgument(0, $poolMap);
    }

    private function registerConnectionPool(string $serviceId, array $tags)
    {
        $poolNames = [];
        foreach ($tags as $tag) {
            $objTag = ConnectionPoolTag::fromArray($tag, $serviceId);
            $poolNames[] = $objTag->pool();
        }

        return $poolNames;
    }
}