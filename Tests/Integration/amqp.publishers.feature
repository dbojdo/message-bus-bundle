Feature: AMQP Publisher configuration
  In order to integrate AMQP infrastructure with Symfony
  As a developer
  I want to configure AMQP publishers using Dependency Injection Config component

  Background:
    Given the configuration contains:
    """
    framework:
        secret: "my-secret-hash"
    """

  Scenario: AMQP Publisher config
    Given the configuration contains:
    """
    webit_message_bus:
        amqp:
            connection_pools:
                pool_1:
                  connection_11:
                      host: rabbitmq
                      port: 5423
                      username: rabbitmq
                      password: rabbitmq
                      vhost: /
        publishers:
            my_amqp_publisher:
                amqp:
                    target:
                        pool: pool_1
                        exchange: my-exchange
    """
    When I bootstrap the application
    Then then the following AMQP publishers should be available
    """
    my_amqp_publisher
    """