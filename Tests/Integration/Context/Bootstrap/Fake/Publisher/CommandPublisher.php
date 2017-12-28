<?php

namespace Webit\MessageBusBundle\Tests\Integration\Context\Bootstrap\Fake\Publisher;

use Webit\MessageBus\Exception\MessagePublicationException;
use Webit\MessageBus\Message;
use Webit\MessageBus\Publisher;

class CommandPublisher implements Publisher
{
    /** @var string */
    private $cacheDir;

    /**
     * CommandPublisher constructor.
     * @param string $cacheDir
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param Message $message
     * @throws MessagePublicationException
     */
    public function publish(Message $message)
    {
        file_put_contents(
            $filename = sprintf('%s/%s.data', $this->cacheDir, md5(time().mt_rand(0, mt_getrandmax()))),
            $message->type()."\n".$message->content()
        );

        echo sprintf('Published to: %s', $filename)."\n";
    }
}
