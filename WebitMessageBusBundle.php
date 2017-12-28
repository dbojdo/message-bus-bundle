<?php

namespace Webit\MessageBusBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Compiler\AmqpConnectionPoolPass;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Compiler\AmqpListenerPass;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Compiler\AmqpPublisherPass;
use Webit\MessageBusBundle\DependencyInjection\Amqp\Extension\AmqpExtensionHelper;
use Webit\MessageBusBundle\DependencyInjection\Compiler\PublisherPass;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Compiler\EventDispatcherPublisherPass;
use Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Extension\EventDispatcherExtensionHelper;

class WebitMessageBusBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PublisherPass());

        if (AmqpExtensionHelper::hasAmqp()) {
            $container->addCompilerPass(new AmqpConnectionPoolPass());
            $container->addCompilerPass(new AmqpPublisherPass());
            $container->addCompilerPass(new AmqpListenerPass());
        }

        if (EventDispatcherExtensionHelper::hasEventDispatcher()) {
            $container->addCompilerPass(new EventDispatcherPublisherPass());
        }
    }
}
