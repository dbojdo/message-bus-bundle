<?php

namespace Webit\MessageBusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webit\MessageBus\Exception\UnregisteredPublisherException;
use Webit\MessageBus\Message;
use Webit\MessageBus\Publisher;

class PublishCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('webit_message_bus:publish');
        $this->addArgument('publisher', InputArgument::REQUIRED, 'Publisher name');
        $this->addArgument('messageType', InputArgument::REQUIRED, 'Message Type');
        $this->addArgument(
            'messageContent',
            InputArgument::OPTIONAL,
            'Message Content',
            ''
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $publisherName = $input->getArgument('publisher');
        try {
            /** @var Publisher $publisher */
            $publisher = $this->getContainer()
                            ->get('webit_message_bus.publisher_registry')
                            ->getPublisher($publisherName);
        } catch (UnregisteredPublisherException $e) {
            $output->writeln(
                sprintf('<error>Requested publisher "%s" is not registered.</error>', $publisherName)
            );
            return 1;
        }

        $publisher->publish(
            new Message($input->getArgument('messageType'), $input->getArgument('messageContent'))
        );

        $output->write('<info>The message has been published.</info>');
    }
}
