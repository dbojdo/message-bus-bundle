<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp;

use Webit\MessageBusBundle\DependencyInjection\Tag\IncompleteTagDefinitionException;

final class ListenerTag
{
    /** @var string */
    private $listener;

    /**
     * @return string
     */
    public static function name()
    {
        return 'webit_message_bus.amqp.listener';
    }

    /**
     * @param array $options
     * @param string $serviceId
     * @return ListenerTag
     */
    public static function fromArray(array $options, string $serviceId)
    {
        if (!isset($options['listener'])) {
            throw IncompleteTagDefinitionException::createForMissingOption($serviceId, self::name(), 'listener');
        }

        return new self($options['listener']);
    }

    public function __construct(string $listener)
    {
        $this->listener = $listener;
    }

    /**
     * @return string
     */
    public function listener()
    {
        return $this->listener;
    }

    /**
     * @return string[]
     */
    public function options()
    {
        return [
            'listener' => $this->listener()
        ];
    }
}