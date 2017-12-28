<?php

namespace Webit\MessageBusBundle\DependencyInjection\EventDispatcher\Compiler;

use Webit\MessageBusBundle\DependencyInjection\Tag\IncompleteTagDefinitionException;

final class EventFromMessageFactoryTag
{
    /** @var string */
    private $event;

    /** @var string */
    private $publisher;

    /**
     * EventDispatcherEventFromMessageFactoryTag constructor.
     * @param string $event
     * @param string $publisher
     */
    public function __construct(string $event, string $publisher)
    {
        $this->event = $event;
        $this->publisher = $publisher;
    }

    public static function name()
    {
        return 'webit_message_bus.event_dispatcher.publisher.event_from_message_factory';
    }

    /**
     * @param array $options
     * @param string $serviceId
     * @return EventFromMessageFactoryTag
     */
    public static function fromArray(array $options, string $serviceId)
    {
        if (!isset($options['publisher'])) {
            throw IncompleteTagDefinitionException::createForMissingOption($serviceId, self::name(), 'publisher');
        }

        if (!isset($options['event'])) {
            throw IncompleteTagDefinitionException::createForMissingOption($serviceId, self::name(), 'event');
        }

        return new self(
            $options['event'],
            $options['publisher']
        );
    }

    /**
     * @return string
     */
    public function event(): string
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function publisher(): string
    {
        return $this->publisher;
    }

    public function options()
    {
        return [
            'event' => $this->event,
            'publisher' => $this->publisher
        ];
    }
}