<?php

namespace Webit\MessageBusBundle\DependencyInjection\Tag;

class IncompleteTagDefinitionException extends \RuntimeException
{
    public static function createForMissingOption(
        string $serviceId,
        string $tagName,
        string $option
    ): IncompleteTagDefinitionException {
        return new self(
            sprintf(
                'Service "%s" tagged as "%s" has a missing mandatory tag option "%s"',
                $serviceId,
                $tagName,
                $option
            )
        );
    }
}
