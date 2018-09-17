<?php

namespace Webit\MessageBusBundle\DependencyInjection\Compiler;

use Webit\MessageBusBundle\DependencyInjection\Tag\IncompleteTagDefinitionException;

final class PublisherTag
{
    /** @var string */
    private $publisher;

    /**
     * @return string
     */
    public static function name()
    {
        return 'webit_message_bus.publisher';
    }

    /**
     * @param array $options
     * @param string $serviceId
     * @return PublisherTag
     */
    public static function fromArray(array $options, string $serviceId)
    {
        if (!isset($options['publisher'])) {
            throw IncompleteTagDefinitionException::createForMissingOption($serviceId, self::name(), 'pool');
        }

        return new self($options['publisher']);
    }

    public function __construct(string $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @return string
     */
    public function publisher()
    {
        return $this->publisher;
    }

    /**
     * @return string[]
     */
    public function options()
    {
        return [
            'publisher' => $this->publisher()
        ];
    }
}