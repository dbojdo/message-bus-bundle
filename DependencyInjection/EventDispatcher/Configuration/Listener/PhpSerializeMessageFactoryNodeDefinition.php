<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class PhpSerializeMessageFactoryNodeDefinition extends ArrayNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('php', $parent);

        $this
            ->children()
                ->append(new MessageTypeResolverNodeDefinition())
                ->append(new MessageContentProviderNodeDefinition())
            ->end();
    }
}