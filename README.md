# Tornado Workshop: Asynchronous programming and Generators in Php
 
## Requirements

* [`php >=7.0`](https://secure.php.net/manual/en/install.php)
 with [CLI](https://secure.php.net/manual/en/features.commandline.introduction.php) support,
* [`php-curl`](https://secure.php.net/manual/en/book.curl.php) extension,
* [`git`](https://git-scm.com/),
* [`composer`](https://getcomposer.org/),
* a connection to Internet.

And of course: patience, perseverance and good mood 🙂.

## Getting Started

### Installation

First, clone this repository, and go in corresponding directory.
```bash
git clone git@github.com:b-viguier/tornado-workshop.git
cd tornado-workshop
```

Then, install dependencies.
```bash
composer install
```

## Workshop

The goal of this workshop is to discover a *text*, composed by *sentences*, composed by *words*, composed by *letters*.
* The first API call gives you the list of ordered sentences:
[https://b-viguier.github.io/Afup-Workshop-Async/api/text.json](https://b-viguier.github.io/Afup-Workshop-Async/api/text.json)
* Each sentence is accessible according to the given relative path: 
[https://b-viguier.github.io/Afup-Workshop-Async/api/sentence/a53441.json](https://b-viguier.github.io/Afup-Workshop-Async/api/sentence/a53441.json) for example.  
* In the same way, you can retrieve details about words composing a sentence:
[https://b-viguier.github.io/Afup-Workshop-Async/api/word/5e2f8e.json](https://b-viguier.github.io/Afup-Workshop-Async/api/word/5e2f8e.json)
* To finish, the value of each letter can be requested:
[https://b-viguier.github.io/Afup-Workshop-Async/api/letter/7fc562.json](https://b-viguier.github.io/Afup-Workshop-Async/api/letter/7fc562.json)

To retrieve the full text, you will need about **8000 requests**.
If each request takes 200ms, it means that the global execution should take about 25 minutes.
To be honest, I never had the patience to wait until the end 😅,
that's why we will use asynchronous request, to get the result in about 20 seconds.


#### Steps

##### Already coded
* Number of words
* Requests statistics

##### Your turn
* Use TokenBag to limit requests + monitoring
* Retrieve all the text
* Display ordered sentences as soon as ready
* Implement a (in-memory) Cache decorator for the HttpClient
* Create a function returning *Articles* as soon as available.
* ... 

##### Playing with AWS

Install [AWS Php SDK](https://github.com/aws/aws-sdk-php)
```bash
composer require aws/aws-sdk-php
```

The [SDK is already asynchronous](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_promises.html)
but it uses [Guzzle Promises](https://github.com/guzzle/promises) and related
[Http Client](https://github.com/guzzle/guzzle), so we have to create a dedicated adapter.
Hopefully, Tornado provides an HttpClient based on Guzzle, so you can rely on it if you are lost.

GuzzlePromise library has its own event loop that you have to tick regularly in order to process its internal events.
In case of curl asynchronous requests, you also have to deal with the event loop of curl,
that you can [tick thanks to a dedicated function in `CurlMultiHandler`](https://github.com/guzzle/guzzle/blob/master/src/Handler/CurlMultiHandler.php#L101).

Then you'll have to deal with 2 objects: a `GuzzleHttp\Client`, and the underlying `CurlMultiHandler`
and you have to ensure that they are both working together.
To ensure this, Tornado provide a dedicate class [`CurlMultiClientWrapper`](https://github.com/M6Web/Tornado/blob/master/src/Adapter/Guzzle/CurlMultiClientWrapper.php),
feel free to use it.

Keep in mind that each Guzzle client manage its own requests,
so you have to own the client used by the AWS SDK to be sure to be able to process related events.
Furthermore, remember that each AWS client may use its own auto-created Guzzle Client if you don't provide one.
Here an example for creating an S3 Client using an advanced credential mechanism,
you can notice that we inject the same Guzzle Http Client to each AWS client.

```php
<?php 
$eventLoop = new Tornado\Adapter\Tornado\EventLoop();
$wrapper = new Tornado\Adapter\Guzzle\CurlMultiClientWrapper();
$awsHttpHandler = new \Aws\Handler\GuzzleV6\GuzzleHandler($wrapper->getClient());

//Create a S3Client
$s3Client = new S3Client([
    'credentials' => new AssumeRoleCredentialProvider([
        'client' => new StsClient([
            'version' => 'latest',
            'region' => $region,
            'credentials' => CredentialProvider::ini('my_profile'),
            'http_handler' => $awsHttpHandler,
        ]),
        'assume_role_params' => [
            'RoleArn' => 'arn:aws:iam::0000000000:role/my-role',
            'RoleSessionName' => 'my-session',
        ],
    ]),
    'region' => $region,
    'version' => 'latest',
    'http_handler' => $awsHttpHandler,
]);
```

Finally, write a class (service) able to convert a Guzzle promise to a Tornado one.
Here an example of the suggested usage.
```php
<?php

function listBuckets(Tornado\EventLoop $eventLoop, S3Client $s3Client, GuzzleAdapter $adapter): \Generator
{
    $buckets = yield $adapter->adapt($s3Client->listBucketsAsync());
    foreach ($buckets['Buckets'] as $bucket) {
        echo "> {$bucket['Name']}\n";
    }
}
```

What about uploading *Articles* of the mysterious API in an S3 Bucket? 😃
