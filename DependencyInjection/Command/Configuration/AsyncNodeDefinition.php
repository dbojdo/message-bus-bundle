<?php

namespace Webit\MessageBusBundle\DependencyInjection\Command\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class AsyncNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('async', $parent);

        $this
            ->canBeEnabled()
            ->children()
                ->integerNode('max_processes')->defaultValue(5)->end()
            ->end();
    }
}
