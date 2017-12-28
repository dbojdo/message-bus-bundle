<?php

namespace Webit\MessageBusBundle\DependencyInjection\Amqp;

use Webit\MessageBusBundle\DependencyInjection\Tag\IncompleteTagDefinitionException;

final class ConnectionPoolTag
{
    /** @var string */
    private $pool;

    /**
     * @return string
     */
    public static function name()
    {
        return 'webit_message_bus.amqp.connection_pool';
    }

    /**
     * @param array $options
     * @param string $serviceId
     * @return ConnectionPoolTag
     */
    public static function fromArray(array $options, string $serviceId)
    {
        if (!isset($options['pool'])) {
            throw IncompleteTagDefinitionException::createForMissingOption($serviceId, self::name(), 'pool');
        }

        return new self($options['pool']);
    }

    public function __construct(string $poolName)
    {
        $this->pool = $poolName;
    }

    /**
     * @return string
     */
    public function pool()
    {
        return $this->pool;
    }

    /**
     * @return string[]
     */
    public function options()
    {
        return [
            'pool' => $this->pool()
        ];
    }
}