<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap;

use Doctrine\Common\Annotations\AnnotationRegistry;

final class AnnotationReaderRegistrar
{
    /** @var bool */
    private static $registered = false;

    public static function register()
    {
        if (!self::$registered) {
            $loader = include __DIR__.'/../../../../vendor/autoload.php';
            AnnotationRegistry::registerLoader([$loader, 'loadClass']);
            self::$registered = true;
        }
    }
}