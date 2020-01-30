<?php

namespace Workshop\Async;

use M6Web\Tornado\EventLoop;
use M6Web\Tornado\Promise;

class AsyncScreen
{

    public function __construct(EventLoop $eventLoop, Measurable ...$measurables)
    {
        $this->eventLoop = $eventLoop;
        $this->measurables = $measurables;
    }

    public function display(): Promise
    {
        $this->startTime = microtime(true);

        return $this->eventLoop->async($this->loop());
    }

    public function __destruct()
    {
        $this->isAlive = false;
    }

    /** @var EventLoop */
    private $eventLoop;

    /** @var array<Measurable>  */
    private $measurables;

    /** @var int */
    private $startTime = 0;

    private $isAlive = true;

    private function loop(): \Generator
    {
        while ($this->isAlive) {
            yield $this->eventLoop->idle();

            $elapsedTime = number_format(microtime(true) - $this->startTime, 1);

            echo "Time: $elapsedTime\t";
            foreach ($this->measurables as $measurable) {
                foreach ($measurable->getMetrics() as $name => $value) {
                    echo "$name: $value\t";
                }
            }
            echo "\r";
        }
    }
}
