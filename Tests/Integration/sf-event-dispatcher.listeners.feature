Feature: AMQP Publisher configuration
  In order to integrate Message Bus Symfony Event Dispatcher infrastructure with Symfony
  As a developer
  I want to configure Event Dispatcher listeners using Dependency Injection Config component

  Background:
    Given the configuration contains:
    """
    framework:
        secret: "my-secret-hash"
    """

  Scenario: Event Dispatcher listener with custom consumer config
    Given the configuration contains:
    """
    services:
        mocked.my_consumer:
            synthetic: true
            public: true
        my_consumer:
            class: Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\SpyableConsumer
            arguments: ["@mocked.my_consumer"]
        event3_to_message:
            class: Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event3ToMessage

    jms_serializer: ~

    webit_message_bus:
        listeners:
            my_events_listener:
                event_dispatcher:
                    consumer: my_consumer
                    message_factories:
                        -
                            jms: ~
                            supports: [event1]
                        -
                            php: ~
                            supports: [event2]
                        -
                            service: event3_to_message
                            supports: [event3]
    """
    When I bootstrap the application
    Then the event "event1" dispatched to "event_dispatcher" event dispatcher should be consumed by the "mocked.my_consumer" consumer
    And the event "event2" dispatched to "event_dispatcher" event dispatcher should be consumed by the "mocked.my_consumer" consumer
    And the event "event3" dispatched to "event_dispatcher" event dispatcher should be consumed by the "mocked.my_consumer" consumer

  Scenario: Event Dispatcher Listener -  forward to other publisher
    Given the configuration contains:
    """
    services:
        mocked.my_publisher:
            synthetic: true
            public: true
            tags:
                - { name: webit_message_bus.publisher, publisher: my_publisher }
        event3_to_message:
            class: Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event3ToMessage

    webit_message_bus:
        listeners:
            my_events_listener:
                event_dispatcher:
                    forward_to: my_publisher
                    message_factories:
                        -
                            jms: ~
                            supports: [event1]
                        -
                            php: ~
                            supports: [event2]
                        -
                            service: event3_to_message
                            supports: [event3]
    """
    When I bootstrap the application
    Then the event "event1" dispatched to "event_dispatcher" event dispatcher should be forwarded to "mocked.my_publisher" publisher
    Then the event "event2" dispatched to "event_dispatcher" event dispatcher should be forwarded to "mocked.my_publisher" publisher
    Then the event "event3" dispatched to "event_dispatcher" event dispatcher should be forwarded to "mocked.my_publisher" publisher
