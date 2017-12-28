Feature: AMQP Publisher configuration
  In order to integrate AMQP infrastructure with Symfony
  As a developer
  I want to configure AMQP listeners using Dependency Injection Config component

  Background:
    Given the configuration contains:
    """
    framework:
        secret: "my-secret-hash"

    webit_message_bus:
        amqp:
            connection_pools:
                pool_1:
                  connection_11:
                      host: rabbitmq
                      port: 5672
                      username: rabbitmq
                      password: rabbitmq
                      vhost: /
    """

  Scenario: AMQP Listener with custom consumer config
    Given the configuration contains:
    """
    services:
        mocked.my_consumer:
            synthetic: true
        my_consumer:
            class: Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\SpyableConsumer
            arguments: ["@mocked.my_consumer"]

    webit_message_bus:
        listeners:
            my_amqp_listener:
                amqp:
                    pool: pool_1
                    queue: my_queue
                    consumer: my_consumer
    """
    When I bootstrap the application
    Then then the following AMQP listeners should be available
    """
    my_amqp_listener
    """
    And the amqp listener "my_amqp_listener" should consume messages from the "pool_1:my_queue" queue using "mocked.my_consumer" consumer

  Scenario: AMQP Listener -  forward to other publisher
    Given the configuration contains:
    """
    services:
        mocked.my_publisher:
            synthetic: true
            tags:
                - { name: webit_message_bus.publisher, publisher: my_publisher }

    webit_message_bus:
        listeners:
            my_amqp_listener:
                amqp:
                    pool: pool_1
                    queue: my_queue_2
                    forward_to: my_publisher
    """
    When I bootstrap the application
    Then then the following AMQP listeners should be available
    """
    my_amqp_listener
    """
    And the listener "my_amqp_listener" should forward messages from the "pool_1:my_queue_2" queue to the "mocked.my_publisher" publisher
