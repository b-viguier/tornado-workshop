<?php

namespace Workshop\Async;

use M6Web\Tornado\Deferred;
use M6Web\Tornado\Promise;
use M6Web\Tornado\EventLoop;

class TokenBag
{
    /** @var EventLoop */
    private $eventLoop;

    /** @var int */
    private $nbTokens = 0;

    /** @var Deferred[] */
    private $deferredList = [];

    public function __construct(EventLoop $eventLoop, int $nbTokens)
    {
        $this->eventLoop = $eventLoop;
        $this->nbTokens = $nbTokens;
    }

    public function acquireToken(): Promise
    {
        if ($this->nbTokens > 0) {
            --$this->nbTokens;

            return $this->eventLoop->promiseFulfilled(true);
        }

        $deferred = $this->eventLoop->deferred();
        $this->deferredList[] = $deferred;

        return $deferred->getPromise();
    }

    public function releaseToken()
    {
        ++$this->nbTokens;

        if (count($this->deferredList)) {
            $deferred = array_pop($this->deferredList);
            --$this->nbTokens;
            $deferred->resolve(true);
        }
    }
}
