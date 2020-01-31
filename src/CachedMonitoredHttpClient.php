<?php


namespace Workshop\Async;

use M6Web\Tornado\HttpClient;
use M6Web\Tornado\Promise;
use Psr\Http\Message\RequestInterface;

class CachedMonitoredHttpClient implements HttpClient, Measurable
{

    /** @var array */
    private $cache = [];

    /**
     * @var HttpClient
     */
    private $monitoredHttpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->monitoredHttpClient = $httpClient;
    }

    public function getMetrics(): array
    {
        return [
            'cache' => str_pad(count($this->cache), 4, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): Promise
    {
        $path = $request->getUri()->getPath();

        if (!isset($this->cache[$path])) {
            $this->cache[$path] = $this->monitoredHttpClient->sendRequest($request);
        }

        return $this->cache[$path];
    }
}