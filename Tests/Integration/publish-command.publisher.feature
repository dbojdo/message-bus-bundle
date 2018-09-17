Feature: Publish command
  In order to integrate Message Bus PublishCommand infrastructure with Symfony
  As a developer
  I want to configure PublishCommandPublisher publishers using Dependency Injection Config component

  Background:
    Given the configuration contains:
    """
    services:
        mocked.event_dispatcher1:
            synthetic: true
            public: true

    framework:
        secret: "my-secret-hash"
    """

  Scenario: Synchronous PublishCommand Publisher config
    Given the configuration contains:
    """
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

    webit_message_bus:
        publishers:
            my_command_publisher:
              command:
                  process_factory:
                      binary_path: "%kernel.root_dir%/bin/console"
                      env_vars:
                          SF_KERNEL_CONFIG: "%kernel.config%"
                          SF_KERNEL_HASH: "%kernel.hash%"
                  forward_to: my_publisher
    """
    When I publish the message to the "my_command_publisher" publisher
    Then the message should be published
    
  Scenario: Asynchronous PublishCommand Publisher config
    Given the configuration contains:
    """
    services:
        messages_logger:
            class: Symfony\Component\HttpKernel\Log\Logger
            arguments: ["info", "%kernel.logs_dir%/messages.log"]

        publisher:
            class: Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\Publisher\SleepingCommandPublisher
            arguments: ["@messages_logger", 1]
            tags:
                - { name: webit_message_bus.publisher, publisher: my_publisher }

    webit_message_bus:
        publishers:
            my_command_publisher:
              command:
                  process_factory:
                      binary_path: "%kernel.root_dir%/bin/console"
                      env_vars:
                          SF_KERNEL_CONFIG: "%kernel.config%"
                          SF_KERNEL_HASH: "%kernel.hash%"
                  forward_to: my_publisher
                  async:
                      max_processes: 2
    """
    When I publish the message to the "my_command_publisher" publisher
    And I publish the message to the "my_command_publisher" publisher
    And I publish the message to the "my_command_publisher" publisher
    Then the messages should be published asynchronously
