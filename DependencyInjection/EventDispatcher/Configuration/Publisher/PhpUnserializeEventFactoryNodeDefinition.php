<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class PhpUnserializeEventFactoryNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('php', $parent);

        $this
            ->children()
                ->append(new EventNameResolverNodeDefinition())
            ->end();
    }
}