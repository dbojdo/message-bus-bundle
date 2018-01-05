<?php

namespace Webit\MessageBusBundle\DependencyInjection\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class PublicationExceptionNodeDefinition extends PublisherTypeExceptionNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('on_exception', $parent);

        $this
            ->beforeNormalization()
                ->ifTrue(function ($node) {
                    return !isset($node['unsupported_message']);
                })
                ->then(function ($node) {
                    $normalised = $node;

                    unset($node['default']);

                    $normalised['unsupported_message'] = $node;

                    return $normalised;
                })
            ->end()
            ->beforeNormalization()
                ->ifTrue(function ($node) {
                    return !isset($node['default']);
                })
                ->then(function ($node) {
                    $normalised = $node;

                    unset($node['unsupported_message']);

                    $normalised['default'] = $node;

                    return $normalised;
                })
            ->end()
            ->validate()
                ->always(function ($node) {
                    unset($node['strategy']);
                    unset($node['logger']);
                    unset($node['service']);

                    return $node;
                })
            ->end()
            ->children()
                ->append(new PublisherTypeExceptionNodeDefinition('unsupported_message'))
                ->append(new PublisherTypeExceptionNodeDefinition('default'))
            ->end();
    }
}
