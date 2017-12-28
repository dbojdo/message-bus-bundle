<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class JmsMessageFactoryNodeDefinition extends ArrayNodeDefinition
{
    /**
     * @param NodeParentInterface|null $parent
     */
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('jms', $parent);

        $this
            ->children()
                ->append(new MessageTypeResolverNodeDefinition())
                ->append(new MessageContentProviderNodeDefinition())
                ->scalarNode('serializer')->defaultValue('jms_serializer')->cannotBeEmpty()->end()
                ->enumNode('format')->values(['json', 'xml'])->defaultValue('json')->cannotBeEmpty()->end()
            ->end();
    }
}
