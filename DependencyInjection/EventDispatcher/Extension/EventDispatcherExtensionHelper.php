<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\EventDispatcherPublisher;

final class EventDispatcherExtensionHelper
{
    /** @var ContainerBuilder */
    private $container;

    /** @var XmlFileLoader */
    private $loader;

    /** @var PublisherHelper */
    private $publisherHelper;

    /** @var ListenerHelper */
    private $listenerHelper;

    public function __construct(ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->container = $container;
        $this->loader = $loader;
        $this->publisherHelper = new PublisherHelper($container);
        $this->listenerHelper = new ListenerHelper($container);
    }

    public static function hasEventDispatcher(): bool
    {
        return class_exists(EventDispatcherPublisher::class);
    }

    public function configureEventDispatcher()
    {
        $this->loader->load('event_dispatcher.xml');
    }

    public function createPublisher(string $publisherName, array $publisherConfig): Definition
    {
        return $this->publisherHelper->createPublisher($publisherName, $publisherConfig);
    }

    public function createListener(string $listenerName, array $listenerConfig): Definition
    {
        return $this->listenerHelper->createListener($listenerName, $listenerConfig);
    }
}
