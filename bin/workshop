#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use M6Web\Tornado;
use GuzzleHttp\Psr7\Request;

/**
 * Creates a Psr7 Request for the *mysterious* API, from a resource endpoint.
 */
function getRequest(string $endpoint): Request
{
    return new Request('GET', "https://b-viguier.github.io/Afup-Workshop-Async/api/$endpoint");
}

// Tornado Initialization
$eventLoop = new Tornado\Adapter\Amp\EventLoop();
$httpClient = new Tornado\Adapter\Guzzle\HttpClient($eventLoop, new Tornado\Adapter\Guzzle\CurlMultiClientWrapper());


echo "Start!\n";

$promise = $httpClient->sendRequest(getRequest('text.json'));
/** @var \GuzzleHttp\Psr7\Response $response */
$response = $eventLoop->wait($promise);

$data = json_decode((string) $response->getBody(), JSON_OBJECT_AS_ARRAY);
$nbSentences = count($data['sentences']);

echo "There are $nbSentences sentences in the mysterious text…\n";
