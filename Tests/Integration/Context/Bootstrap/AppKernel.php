<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap;

use JMS\SerializerBundle\JMSSerializerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Webit\MessageBusBundle\WebitMessageBusBundle;
use Webit\Tests\Behaviour\Bundle\Kernel;

class AppKernel extends Kernel
{
    /**
     * @inheritdoc
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new JMSSerializerBundle(),
            new WebitMessageBusBundle()
        ];
    }
}
