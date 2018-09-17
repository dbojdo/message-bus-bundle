Feature: AMQP Publisher configuration
  In order to integrate Message Bus Symfony Event Dispatcher infrastructure with Symfony
  As a developer
  I want to configure Event Dispatcher publishers using Dependency Injection Config component

  Background:
    Given the configuration contains:
    """
    services:
        mocked.event_dispatcher1:
            synthetic: true

    framework:
        secret: "my-secret-hash"
    """

  @current
  Scenario: Symfony Event Dispatcher Publisher config
    Given the configuration contains:
    """
    services:
        event3_factory:
            class: \Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event3Factory
        event4_factory:
            class: \Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event4Factory
            tags:
                - { name: webit_message_bus.event_dispatcher.publisher.event_from_message_factory, publisher: my_sf_publisher, event: event4 }

    jms_serializer: ~

    webit_message_bus:
        publishers:
            my_sf_publisher:
              event_dispatcher:
                  dispatcher: mocked.event_dispatcher1
                  event_factories:
                      -
                          jms:
                              types_map:
                                  Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event1: event1
                          supports: [event1]
                      -
                          php: ~
                          supports: [event2]
                          fallback: true
                      -
                          service: event3_factory
                          supports: [event3]
    """
    When I bootstrap the application
    Then the message "event1" should be publishable over "my_sf_publisher" Symfony Event Dispatcher publisher
    And the message "event2" should be publishable over "my_sf_publisher" Symfony Event Dispatcher publisher
    And the message "event3" should be publishable over "my_sf_publisher" Symfony Event Dispatcher publisher
    And the message "event4" should be publishable over "my_sf_publisher" Symfony Event Dispatcher publisher
