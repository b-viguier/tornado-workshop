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
git clone git@github.m6web.fr:b-viguier/tornado-workshop.git
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
* ... 
