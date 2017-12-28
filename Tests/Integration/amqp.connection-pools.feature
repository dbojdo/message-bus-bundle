Feature: Connection Pools configuration
  In order to integrate AMQP infrastructure with Symfony
  As a developer
  I want to configure AMQP publishers using Dependency Injection Config component

  Background:
    Given the configuration contains:
    """
    framework:
        secret: "my-secret-hash"
    """

  Scenario: AMQP Connection Pools set up
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
                    connection_21:
                        host: rabbitmq2
                        port: 5423
                        username: rabbitmq2
                        password: rabbitmq2
                        vhost: /ble
                pool_2:
                  connection_21:
                      host: rabbitmq
                      port: 5423
                      username: rabbitmq
                      password: rabbitmq
                      vhost: /
    """
    When I bootstrap the application
    Then the following AMQP connection pools should be available
    """
    pool_1,pool_2
    """