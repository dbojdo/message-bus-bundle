<?php

namespace Webit\MessageBusBundle\DependencyInjection\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

abstract class AbstractPublisherNodeDefinition extends ArrayNodeDefinition
{
    public function __construct($name, NodeParentInterface $parent = null)
    {
        parent::__construct($name, $parent);

//        $this->children()->append(new PublicationExceptionNodeDefinition());
    }
}
