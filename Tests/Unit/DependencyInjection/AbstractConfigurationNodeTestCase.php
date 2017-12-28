<?php

namespace Webit\MessageBusBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Webit\Tests\Unit\RandomValuesTrait;

abstract class AbstractConfigurationNodeTestCase extends TestCase
{
    use RandomValuesTrait;

    /** @var NodeInterface */
    protected $node;

    protected function setUp()
    {
        $this->node = $this->buildRootNode($this->createTestedNode());
    }

    abstract protected function createTestedNode(): ParentNodeDefinitionInterface;

    protected function buildRootNode(ParentNodeDefinitionInterface $node)
    {
        $builder = new TreeBuilder();
        $builder
            ->root('root')
            ->append($node);

        return $builder->buildTree();
    }

    /**
     * @param string $config
     * @return array
     */
    protected function processConfig(string $config)
    {
        $processor = new Processor();
        return $processor->process($this->node, [$expectedConfig = Yaml::parse($config)]);
    }

    protected function randomStringConfig()
    {
        return chr(mt_rand(97, 122)) . $this->randomString(4, 8);
    }
}
