<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Cache\Cache;
use JMS\Serializer\EventDispatcher\Event;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Assert;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webit\MessageBus\Consumer;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Channel\NewChannelConnectionAwareChannelFactory;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Pool\ConnectionPool;
use Webit\MessageBus\Infrastructure\Amqp\Connection\Pool\Registry\ConnectionPoolRegistry;
use Webit\MessageBus\Infrastructure\Amqp\Listener\AmqpListener;
use Webit\MessageBus\Infrastructure\Amqp\Listener\Exception\KillPillReceivedException;
use Webit\MessageBus\Infrastructure\Amqp\Listener\KillPill;
use Webit\MessageBus\Infrastructure\Amqp\Listener\Registry\ListenerRegistry;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\AmqpPublisher;
use Webit\MessageBus\Infrastructure\Amqp\Publisher\Registry\PublisherRegistry;
use Webit\MessageBus\Infrastructure\Amqp\Util\Queue\Queue;
use Webit\MessageBus\Infrastructure\Amqp\Util\Queue\QueueManager;
use Webit\MessageBus\Infrastructure\Symfony\EventDispatcher\Publisher\EventDispatcherPublisher;
use Webit\MessageBus\Message;
use Webit\MessageBus\Publisher;
use Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\AnnotationReaderRegistrar;
use Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\AppKernel;
use Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event1;
use Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event2;
use Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event3;
use Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\EventDispatcher\Event4;
use Webit\Tests\Behaviour\Bundle\BundleConfigurationContext;
use Webit\Tests\Behaviour\Bundle\Kernel;
use Webit\Tests\Unit\RandomValuesTrait;

final class BundleContext extends BundleConfigurationContext
{
    use RandomValuesTrait;

    /** @var Prophet */
    private $prophet;

    /** @var EventDispatcherInterface[]|ObjectProphecy[] */
    private $eventDispatchers = [];

    /** @var Consumer[]|ObjectProphecy */
    private $consumers;

    /** @var Publisher[]|ObjectProphecy */
    private $publishers;

    /** @var array */
    private $queues = [];

    /** @var Message[] */
    private $publishedMessages;

    /** @var array */
    private $execResult;

    /**
     * @param Kernel $kernel
     * @param ContainerInterface|ContainerBuilder $container
     */
    protected function onKernelBoot(Kernel $kernel, ContainerInterface $container)
    {
        $mockedDispatchers = ['mocked.event_dispatcher1'];
        foreach ($mockedDispatchers as $dispatcher) {
            $this->eventDispatchers[$dispatcher] = $this->prophet->prophesize(EventDispatcherInterface::class);
            $container->set($dispatcher, $this->eventDispatchers[$dispatcher]->reveal());
        }

        $consumers = ['mocked.my_consumer'];
        foreach ($consumers as $consumer) {
            $this->consumers[$consumer] = $this->prophet->prophesize(Consumer::class);
            $container->set($consumer, $this->consumers[$consumer]->reveal());
        }

        $publishers = ['mocked.my_publisher'];
        foreach ($publishers as $publisher) {
            $this->publishers[$publisher] = $this->prophet->prophesize(Publisher::class);
            $container->set($publisher, $this->publishers[$publisher]->reveal());
        }
    }

    public function __construct()
    {
        parent::__construct(new AppKernel());
        $this->prophet = new Prophet();
        AnnotationReaderRegistrar::register();
    }

    /**
     * @AfterScenario
     */
    public function checkPredictions()
    {
        $this->prophet->checkPredictions();
    }

    /**
     * @Then the following AMQP connection pools should be available
     */
    public function theFollowingAmqpConnectionPoolsShouldBeAvailable(PyStringNode $strConnectionPools)
    {
        $pools = explode(',', $strConnectionPools->getRaw());
        $container = $this->kernel->getContainer();

        /** @var ConnectionPoolRegistry $connectionPoolsRegistry */
        $connectionPoolsRegistry = $container->get('webit_message_bus.amqp.connection_pools');

        foreach ($pools as $poolName) {
            Assert::assertInstanceOf(
                ConnectionPool::class,
                $connectionPoolsRegistry->connectionPool($poolName)
            );
        }
    }

    /**
     * @Then then the following AMQP publishers should be available
     */
    public function thenTheFollowingAmqpPublishersShouldBeAvailable(PyStringNode $strAmqpPublishers)
    {
        $publishers = explode(',', $strAmqpPublishers->getRaw());
        $container = $this->kernel->getContainer();

        /** @var PublisherRegistry $amqpPublisherRegistry */
        $amqpPublisherRegistry = $container->get('webit_message_bus.amqp.publishers');

        /** @var \Webit\MessageBus\PublisherRegistry $amqpPublisherRegistry */
        $publisherRegistry = $container->get('webit_message_bus.publisher_registry');

        foreach ($publishers as $publisherName) {
            Assert::assertInstanceOf(
                AmqpPublisher::class,
                $amqpPublisherRegistry->publisher($publisherName)
            );

            Assert::assertInstanceOf(
                AmqpPublisher::class,
                $publisherRegistry->getPublisher($publisherName)
            );
        }
    }

    /**
     * @Then then the following Symfony Event Dispatcher publishers should be available
     */
    public function thenTheFollowingSymfonyEventDispatcherPublishersShouldBeAvailable(PyStringNode $strPublishers)
    {
        $publishers = explode(',', $strPublishers->getRaw());
        $container = $this->kernel->getContainer();

        /** @var \Webit\MessageBus\PublisherRegistry $amqpPublisherRegistry */
        $publisherRegistry = $container->get('webit_message_bus.publisher_registry');

        foreach ($publishers as $publisherName) {
            Assert::assertInstanceOf(
                EventDispatcherPublisher::class,
                $publisherRegistry->getPublisher($publisherName)
            );
        }
    }

    /**
     * @param string $message
     * @return Message
     * @Transform :message
     */
    public function transformMessage($message)
    {
        @list($type, $content) = explode('::', $message, 2);
        return new Message($type, $content);
    }

    /**
     * @param string $publisher
     * @return Publisher
     * @Transform :publisher
     */
    public function transformPublisher($publisher)
    {
        $this->iBootstrapTheApplication();

        $container = $this->kernel->getContainer();

        /** @var \Webit\MessageBus\PublisherRegistry $amqpPublisherRegistry */
        $publisherRegistry = $container->get('webit_message_bus.publisher_registry');

        return $publisherRegistry->getPublisher($publisher);
    }

    /**
     * @Then the message :messageType should be publishable over :publisher Symfony Event Dispatcher publisher
     * @param string $messageType
     * @param Publisher $publisher
     * @throws Publisher\Exception\MessagePublicationException
     */
    public function theFollowingMessagesShouldBePublishableOverSymfonyEventDispatcherPublisher(
        $messageType,
        Publisher $publisher
    ) {
        list($expectedEvent, $message) = $this->createEventAndMessage($messageType);

        $this->eventDispatchers['mocked.event_dispatcher1']->dispatch($message->type(), $expectedEvent)->shouldBeCalled();
        $publisher->publish($message);
    }

    /**
     * @Then then the following AMQP listeners should be available
     */
    public function thenTheFollowingAmqpListenersShouldBeAvailable(PyStringNode $strListeners)
    {
        $listeners = explode(',', $strListeners->getRaw());
        $container = $this->kernel->getContainer();

        /** @var ListenerRegistry $amqpListenerRegistry */
        $amqpListenerRegistry = $container->get('webit_message_bus.amqp.listeners');

        foreach ($listeners as $publisherName) {
            Assert::assertInstanceOf(
                AmqpListener::class,
                $amqpListenerRegistry->listener($publisherName)
            );
        }
    }

    /**
     * @Transform :amqpListener
     * @param string $listener
     * @return AmqpListener
     */
    public function transformAmqpListener($listener)
    {
        $container = $this->kernel->getContainer();
        /** @var ListenerRegistry $amqpListenerRegistry */
        $amqpListenerRegistry = $container->get('webit_message_bus.amqp.listeners');

        return $amqpListenerRegistry->listener($listener);
    }

    /**
     * @Transform :amqpTargetQueue
     * @param string $queue
     * @return array
     */
    public function transformAmqpTargetQueue($queue)
    {
        list($pool, $queueName) = explode(':', $queue, 2);

        $container = $this->kernel->getContainer();
        /** @var ConnectionPoolRegistry $amqpConnectionPoolRegistry */
        $amqpConnectionPoolRegistry = $container->get('webit_message_bus.amqp.connections');

        $targetQueue = [$amqpConnectionPoolRegistry->connectionPool($pool), $queueName];

        return $targetQueue;
    }

    /**
     * @Transform :service
     * @param string $service
     * @return object
     */
    public function transformService($service)
    {
        $container = $this->kernel->getContainer();

        return $container->get($service);
    }

    /**
     * @Transform :mockedConsumer
     * @param $consumer
     * @return Consumer
     */
    public function transformMockedConsumer($consumer)
    {
        if (isset($this->consumers[$consumer])) {
            return $this->consumers[$consumer];
        }

        throw new \InvalidArgumentException(sprintf('Consumer "%s" is not registered as a mocked consumer.',
            $consumer));
    }

    /**
     * @Transform :mockedPublisher
     * @param $publisher
     * @return Consumer
     */
    public function transformMockedPublisher($publisher)
    {
        if (isset($this->publishers[$publisher])) {
            return $this->publishers[$publisher];
        }

        throw new \InvalidArgumentException(sprintf('Publisher "%s" is not registered as a mocked publisher.',
            $publisher));
    }

    /**
     * @Then the amqp listener :amqpListener should consume messages from the :amqpTargetQueue queue using :mockedConsumer consumer
     * @param AmqpListener $amqpListener
     * @param array $amqpTargetQueue
     * @param Consumer|ObjectProphecy $mockedConsumer
     */
    public function theAmqpListenerShouldConsumeMessagesFromTheQueueUsingConsumer(
        AmqpListener $amqpListener,
        array $amqpTargetQueue,
        ObjectProphecy $mockedConsumer
    ) {
        $queueManager = new QueueManager(
            new NewChannelConnectionAwareChannelFactory(
                $amqpTargetQueue[0]
            )
        );

        $queueManager->declareQueue(new Queue($amqpTargetQueue[1], false, true));

        $expectedMessage = new Message($this->randomString(), $this->randomString());

        $queueManager->publishMessage(
            $amqpTargetQueue[1],
            new AMQPMessage($expectedMessage->content(), ['type' => $expectedMessage->type()])
        );

        $queueManager->publishMessage($amqpTargetQueue[1], KillPill::create());

        $mockedConsumer->consume($expectedMessage)->shouldBeCalled();

        try {
            $amqpListener->listen(5);
        } catch (KillPillReceivedException $e) {
        }
    }

    /**
     * @AfterScenario
     */
    public function tearDownAmqpQueues()
    {
        foreach ($this->queues as $targetQueue) {
            list($pool, $queueName) = $targetQueue;
            $queueManager = new QueueManager(
                new NewChannelConnectionAwareChannelFactory($pool)
            );

            $queueManager->deleteQueue($queueName);
        }
    }

    /**
     * @Then the listener :amqpListener should forward messages from the :amqpTargetQueue queue to the :mockedPublisher publisher
     * @param AmqpListener $amqpListener
     * @param array $amqpTargetQueue
     * @param ObjectProphecy $mockedPublisher
     */
    public function theListenerShouldForwardMessagesFromTheQueueToThePublisher(
        AmqpListener $amqpListener,
        array $amqpTargetQueue,
        ObjectProphecy $mockedPublisher
    ) {
        $queueManager = new QueueManager(
            new NewChannelConnectionAwareChannelFactory(
                $amqpTargetQueue[0]
            )
        );

        $queueManager->declareQueue(new Queue($amqpTargetQueue[1], false, true));

        $expectedMessage = new Message($this->randomString(), $this->randomString());

        $queueManager->publishMessage(
            $amqpTargetQueue[1],
            new AMQPMessage($expectedMessage->content(), ['type' => $expectedMessage->type()])
        );

        $queueManager->publishMessage($amqpTargetQueue[1], KillPill::create());

        $mockedPublisher->publish($expectedMessage)->shouldBeCalled();

        try {
            $amqpListener->listen(5);
        } catch (KillPillReceivedException $e) {
        }
    }

    /**
     * @Then the event :eventName dispatched to :service event dispatcher should be consumed by the :mockedConsumer consumer
     * @param string $eventName
     * @param EventDispatcherInterface $service
     * @param ObjectProphecy|Consumer $mockedConsumer
     * @throws Consumer\Exception\MessageConsumptionException
     */
    public function theEventDispatcherListenerShouldConsumeEventsFromTheEventDispatcherUsingConsumer(
        string $eventName,
        EventDispatcherInterface $service,
        ObjectProphecy $mockedConsumer
    ) {
        list($event, $expectedMessage) = $this->createEventAndMessage($eventName);
        $mockedConsumer->consume($expectedMessage)->shouldBeCalled();
        $service->dispatch($eventName, $event);
    }

    /**
     * @Then the event :eventName dispatched to :service event dispatcher should be forwarded to :mockedPublisher publisher
     */
    public function theEventDispatchedToEventDispatcherShouldBeForwardedToPublisher(string $eventName, EventDispatcherInterface $service, ObjectProphecy $mockedPublisher)
    {
        list($event, $expectedMessage) = $this->createEventAndMessage($eventName);
        $mockedPublisher->publish($expectedMessage)->shouldBeCalled();
        $service->dispatch($eventName, $event);
    }

    private function createEventAndMessage($eventName): array
    {
        switch($eventName) {
            case 'event1':
                $event = new Event1($x = $this->randomString());
                $expectedMessage = new Message($eventName, $this->kernel->getContainer()->get('jms_serializer')->serialize($event, 'json'));
                break;
            case 'event2':
                $event = new Event2($x = $this->randomString());
                $expectedMessage = new Message($eventName, serialize($event));
                break;
            case 'event3':
                $event = new Event3($x = $this->randomString());
                $expectedMessage = new Message($eventName, $x);
                break;
            case 'event4':
                $event = new Event4(new Event1($this->randomString()));
                $expectedMessage = new Message($eventName, $event->innerEvent()->x());
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Event of name "%s" is not supported.', $eventName)
                );
        }

        return [$event, $expectedMessage];
    }

    /**
     * @When I publish the message to the :publisher publisher
     */
    public function iPublishTheMessageToThePublisher(Publisher $publisher)
    {
        $this->iBootstrapTheApplication();

        $this->publishedMessages[] = $message = new Message($this->randomString(4, 12), $this->randomString(4, 12));

        $publisher->publish($message);
    }

    /**
     * @When I publish the message to the :publisherName publisher using command
     */
    public function iPublishTheMessageToThe(string $publisherName)
    {
        $this->iBootstrapTheApplication();

        $this->publishedMessages[] = $message = new Message($this->randomString(4, 12), $this->randomString(4, 12));
        $command = sprintf(
            __DIR__.'/Bootstrap/bin/console webit_message_bus:publish %s %s %s',
            escapeshellarg($publisherName),
            escapeshellarg($message->type()),
            escapeshellarg($message->content())
        );

        exec($command, $output, $exitCode);
        $this->execResult = [$exitCode, implode("\n", (array)$output)];
    }

    /**
     * @Then the message should be published
     */
    public function theMessageShouldBePublished()
    {
        /** @var Cache $cache */
        $cache = $this->kernel->getContainer()->get('message_cache');

        Assert::assertTrue($cache->contains('message_1'));
        Assert::assertEquals($this->publishedMessages[0], $cache->fetch('message_1'));
    }

    /**
     * @Then the messages should be published asynchronously
     */
    public function theMessagesShouldBePublishedAsynchronously()
    {
        $maxWait = 3 * 1000000;
        $waited = 0;
        do {
            $allGood = $this->checkMessages();
            if (!$allGood) {
                usleep(500000);
                $waited += 500000;
            }
        } while(!$allGood && $waited < $maxWait);

        Assert::assertTrue($allGood, 'Not all of the messages has been published correctly.');
    }

    private function checkMessages()
    {
        $file = file_get_contents($logFile = $this->kernel->getContainer()->getParameter('kernel.logs_dir'). '/messages.log');

        $allDone = true;
        foreach ($this->publishedMessages as $message) {
            if (false ===strpos($file, sprintf('[%s:%s] Done', $message->type(), $message->content()))) {
                $allDone = false;
                break;
            }
        }

        return $allDone;
    }
}
