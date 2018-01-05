<?php

namespace Webit\MessageBusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Webit\MessageBus\Consumer\Exception\Handler\ByClassExceptionHandler;
use Webit\MessageBus\Consumer\Exception\Handler\IgnoringExceptionHandler;
use Webit\MessageBus\Consumer\Exception\Handler\LoggingExceptionHandler;
use Webit\MessageBus\Consumer\Exception\Handler\PublishingExceptionHandler;
use Webit\MessageBus\Consumer\Exception\Handler\ThrowingExceptionHandler;
use Webit\MessageBus\Consumer\ExceptionHandlingConsumer;
use Webit\MessageBus\Consumer\PublishingConsumer;
use Webit\MessageBus\Consumer\VoidConsumer;
use Webit\MessageBus\Publisher;

class ConsumerExtensionHelper
{
    public function createConsumer(array $consumerConfig)
    {
        switch (true) {
            case isset($consumerConfig['service']):
                $consumer = new Reference($consumerConfig['service']);
                break;
            case isset($consumerConfig['forward_to']):
                $consumer = $this->createForwardingConsumer($consumerConfig['forward_to']);
                break;
            default:
                $consumer = new Definition(VoidConsumer::class);
        }

        if (isset($consumerConfig['on_exception'])) {
            $consumer = $this->wrapWithExceptionHandler($consumer, $consumerConfig['on_exception']);
        }

        return $consumer;
    }

    private function createForwardingConsumer($publisherName): Definition
    {
        $publisher = $this->getPublisher($publisherName);

        return new Definition(
            PublishingConsumer::class,
            [
                $publisher
            ]
        );
    }

    /**
     * @param Definition|Reference $consumer
     * @param array
     * @return Definition
     */
    private function wrapWithExceptionHandler($consumer, array $handlerConfig): Definition
    {
        return new Definition(
            ExceptionHandlingConsumer::class, [
                $consumer,
                new Definition(
                    ByClassExceptionHandler::class,
                    [
                        $this->createExceptionHandler($handlerConfig['unsupported_message']),
                        $this->createExceptionHandler($handlerConfig['default'])
                    ]
                )
            ]
        );
    }

    /**
     * @param array $handlerConfig
     * @return Definition|Reference
     */
    private function createExceptionHandler(array $handlerConfig)
    {
        if (isset($handlerConfig['service'])) {
            return new Reference($handlerConfig['service']);
        }

        switch ($handlerConfig['strategy']) {
            case 'ignore':
                $handler = new Definition(IgnoringExceptionHandler::class);
                break;
            case 'throw':
                $handler = new Definition(ThrowingExceptionHandler::class);
                break;
            default:
                throw new InvalidConfigurationException(
                    sprintf('Unsupported exception handling strategy "%s"', $handlerConfig['strategy'])
                );
        }

        if ($handlerConfig['logger']) {
            $handler = new Definition(
                LoggingExceptionHandler::class,
                [
                    new Reference($handlerConfig['logger']),
                    $handler

                ]
            );
        }

        if ($handlerConfig['forward_to']) {
            $publisher = new Definition(Publisher::class, [$handlerConfig['forward_to']]);
            $publisher->setFactory([new Reference('webit_message_bus.publisher_registry'), 'getPublisher']);

            $handler = new Definition(
                PublishingExceptionHandler::class,
                [
                    $publisher,
                    $handler
                ]
            );
        }

        return $handler;
    }

    private function getPublisher(string $publisherName): Definition
    {
        $publisher = new Definition(Publisher::class, [$publisherName]);
        $publisher->setFactory([new Reference('webit_message_bus.publisher_registry'), 'getPublisher']);

        return $publisher;
    }
}