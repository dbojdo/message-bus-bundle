<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractEvent
 */
abstract class AbstractEvent extends Event
{
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("x")
     */
    private $x;

    public function __construct(string $x)
    {
        $this->x = $x;
    }

    /**
     * @return string
     */
    public function x(): string
    {
        return $this->x;
    }
}