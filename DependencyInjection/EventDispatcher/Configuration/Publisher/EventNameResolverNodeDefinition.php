<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class EventNameResolverNodeDefinition extends ArrayNodeDefinition
{
    public static function types(): array
    {
        return array_merge(self::standardTypes(), ['custom']);
    }

    public static function standardTypes(): array
    {
        return ['from_message_type'];
    }

    /**
     * @param NodeParentInterface|null $parent
     */
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('event_name_resolver', $parent);

        $this
            ->beforeNormalization()
                ->ifEmpty()
                ->then(function ($node) {
                    if ($node === null) {
                        return ['type' => 'from_message_type'];
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
                    ->defaultValue('from_message_type')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('service')->end()
            ->end();
    }
}