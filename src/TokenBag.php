<?php

namespace Workshop\Async;

use M6Web\Tornado\Deferred;
use M6Web\Tornado\HttpClient;
use M6Web\Tornado\Promise;
use M6Web\Tornado\EventLoop;
use Psr\Http\Message\RequestInterface;

class TokenBag implements HttpClient
{
    /** @var EventLoop */
    private $eventLoop;

    /** @var int */
    private $nbTokens = 0;

    /** @var Deferred[] */
    private $deferredList = [];
    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(EventLoop $eventLoop, int $nbTokens, HttpClient $httpClient)
    {
        $this->eventLoop = $eventLoop;
        $this->nbTokens = $nbTokens;
        $this->httpClient = $httpClient;
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

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): Promise
    {
        return
            $this->eventLoop->async((function($request) {
                yield $this->acquireToken();
                try {
                    return yield $this->httpClient->sendRequest($request);
                } finally {
                    $this->releaseToken();
                }
            })($request))
        ;

    }
}
