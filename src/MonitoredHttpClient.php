<?php

namespace Workshop\Async;

use M6Web\Tornado\EventLoop;
use M6Web\Tornado\HttpClient;
use M6Web\Tornado\Promise;
use Psr\Http\Message\RequestInterface;

class MonitoredHttpClient implements HttpClient, Measurable
{
    public function __construct(EventLoop $eventLoop, HttpClient $httpClient)
    {
        $this->eventLoop = $eventLoop;
        $this->httpClient = $httpClient;
    }

    public function sendRequest(RequestInterface $request): Promise
    {
        return $this->eventLoop->async($this->sendMonitoredRequest($request));
    }

    public function getMetrics(): array
    {
        return [
            'conc' => str_pad($this->concurrency, 4, '0', STR_PAD_LEFT),
        ];
    }

    /** @var EventLoop */
    private $eventLoop;

    /** @var HttpClient */
    private $httpClient;

    /** @var int */
    private $concurrency = 0;

    private function sendMonitoredRequest(RequestInterface $request): \Generator
    {
        ++$this->concurrency;
        $response = yield $this->httpClient->sendRequest($request);
        --$this->concurrency;

        return $response;
    }
}
