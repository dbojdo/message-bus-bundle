<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Listener;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class MessageTypeResolverNodeDefinition extends ArrayNodeDefinition
{
    public static function types(): array
    {
        return array_merge(self::standardTypes(), ['custom']);
    }

    public static function standardTypes(): array
    {
        return ['from_event_name'];
    }

    /**
     * @param NodeParentInterface|null $parent
     */
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('message_type_resolver', $parent);

        $this
            ->beforeNormalization()
            ->ifEmpty()
                ->then(function ($node) {
                    if ($node === null) {
                        return ['type' => 'from_event_name'];
                    }

                    return $node;
                })
            ->end()
            ->beforeNormalization()
                ->ifString()
                ->then(function ($node) {
                    if (in_array($node, self::standardTypes())) {
                        return ['type' => $node];
                    }

                    return [
                        'type' => 'custom',
                        'service' => $node
                    ];
                })
            ->end()
            ->beforeNormalization()
                ->always(function ($node) {
                    if (isset($node['service']) && !isset($node['type'])) {
                        $node['type'] = 'custom';
                    }

                    return $node;
                })
            ->end()
            ->beforeNormalization()
                ->always(function ($node) {
                    if ($node['type'] != 'custom') {
                        unset($node['service']);
                    }

                    return $node;
                })
            ->end()
            ->validate()
                ->ifTrue(function ($node) {
                    if ($node['type'] == 'custom') {
                        return !isset($node['service']);
                    }

                    return false;
                })
                ->thenInvalid('Type "custom" requires "service" to be set.')
            ->end()
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('type')
                    ->values(self::types())
                    ->defaultValue('from_event_name')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('service')->end()
            ->end();
    }
}