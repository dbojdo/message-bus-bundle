# Message Bus Bundle
Symfony Bundle for Message Bus library

## Installation

Add a dependency to your project

```bash
composer require webit/message-bus-bundle ^1.0.0
```

Register the bundle in your ***AppKernel***

```php
<?php
class AppKernel extends \Symfony\Component\HttpKernel\Kernel
{
    /**
     * @inheritdoc
     */
    public function registerBundles()
    {
        return [
            //...
            new \Webit\MessageBusBundle\WebitMessageBusBundle()
        ];
    }
    
    // ...
}
```

## Supported infrastructure

 * [AMQP (RabbitMQ)](https://github.com/dbojdo/message-bus-amqp)
 * [Symfony Event Dispatcher](https://github.com/dbojdo/message-bus-sf-event-dispatcher)
 * Symfony Command (bundle build-in)

## Configuration Reference 

 TODO
 
## Running tests

```bash
docker-compose run --rm composer
docker-compose run --rm phpunit
webit_message_bus.amqp.listeners
```
