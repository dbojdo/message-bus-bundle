Feature: Publish command
  In order to publish messages using console command
  As a developer
  I want PublishCommnad to be registered in the Dependency Injection container

  Background:
    Given the configuration contains:
    """
    framework:
        secret: "my-secret-hash"

    services:
        message_cache:
            class: Doctrine\Common\Cache\FilesystemCache
            arguments: ["%kernel.cache_dir%/messages"]
            public: true

        publisher:
            class: Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\Publisher\CommandPublisher
            arguments: ["@message_cache"]
            tags:
                - { name: webit_message_bus.publisher, publisher: my_publisher }
    """

  Scenario: Publish command
    Given the configuration contains:
    """
    webit_message_bus: ~
    """
    When I publish the message to the "my_publisher" publisher using command
    Then the message should be published
