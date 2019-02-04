<?php

namespace Workshop\Async;

use M6Web\Tornado\EventLoop;
use M6Web\Tornado\Promise;

class AsyncScreen
{

    public function __construct(EventLoop $eventLoop, MonitoredHttpClient $monitoredHttpClient)
    {
        $this->eventLoop = $eventLoop;
        $this->monitoredHttpClient = $monitoredHttpClient;
    }

    public function display(): Promise
    {
        $this->startTime = microtime(true);

        return $this->eventLoop->async($this->loop());
    }

    /** @var EventLoop */
    private $eventLoop;

    /** @var MonitoredHttpClient  */
    private $monitoredHttpClient;

    /** @var int */
    private $startTime = 0;

    private function loop(): \Generator
    {
        while (true) {
            yield $this->eventLoop->idle();

            $elapsedTime = round(microtime(true) - $this->startTime, 1);
            $concurrency = $this->monitoredHttpClient->getConcurrency();

            echo "Elapsed: $elapsedTime\tConcurrency: $concurrency\r";
        }
    }
}
