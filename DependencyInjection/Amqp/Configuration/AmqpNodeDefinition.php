<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class AmqpNodeDefinition extends ArrayNodeDefinition
{
    public function __construct($name, NodeParentInterface $parent = null)
    {
        parent::__construct($name, $parent);

        $this
            ->canBeEnabled()
            ->children()
                ->append(new ConnectionPoolsNodeDefinition())
            ->end();
    }
}
