<?php

namespace Webit\MessageBusBundle\Tests\Unit\Publisher\ContainerAwarePublisherRegistry;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webit\MessageBus\Exception\UnregisteredPublisherException;
use Webit\MessageBus\Publisher;
use Webit\MessageBusBundle\Publisher\ContainerAwarePublisherRegistry;
use Webit\Tests\Unit\RandomValuesTrait;

class ContainerAwarePublisherRegistryTest extends TestCase
{
    use RandomValuesTrait;

    /** @var ContainerInterface|ObjectProphecy */
    private $serviceContainer;

    protected function setUp()
    {
        $this->serviceContainer = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     */
    public function itLookupForAServiceInServiceContainer()
    {
        $map = array(
            $publisher1 = $this->randomString(4, 8) => $service1 = $this->randomString(4, 8),
            $publisher2 = $this->randomString(4, 8) => $service2 = $this->randomString(4, 8)
        );

        $registry = new ContainerAwarePublisherRegistry($map);
        $registry->setContainer($this->serviceContainer->reveal());

        $this->serviceContainer->has($service1)->shouldNotBeCalled();
        $this->serviceContainer->has($service2)->willReturn(true);
        $this->serviceContainer
            ->get($service2)
            ->willReturn($publisher = $this->prophesize(Publisher::class)->reveal());

        $this->assertSame($publisher, $registry->getPublisher($publisher2));
    }

    /**
     * @test
     * @expectedException  \Webit\MessageBus\Exception\UnregisteredPublisherException
     */
    public function itThrowsExceptionWhenServiceNotMapped()
    {
        $map = array(
            $publisher1 = $this->randomString(4, 8) => $service1 = $this->randomString(4, 8),
            $publisher2 = $this->randomString(4, 8) => $service2 = $this->randomString(4, 8)
        );

        $registry = new ContainerAwarePublisherRegistry($map);
        $registry->setContainer($this->serviceContainer->reveal());

        $this->serviceContainer->has($service2)->willReturn(false);

        $registry->getPublisher($publisher2);
    }
}
