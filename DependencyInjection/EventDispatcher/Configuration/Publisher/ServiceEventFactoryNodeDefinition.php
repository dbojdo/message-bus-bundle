<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Configuration\Publisher;

use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

class ServiceEventFactoryNodeDefinition extends ScalarNodeDefinition
{
    public function __construct(NodeParentInterface $parent = null)
    {
        parent::__construct('service', $parent);
        $this->cannotBeEmpty();
    }
}