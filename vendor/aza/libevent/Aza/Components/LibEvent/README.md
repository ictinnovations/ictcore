AzaLibEvent
===========

Simple, powerful and easy to use OOP wrapper for the [LibEvent](http://libevent.org/) PHP bindings.

https://github.com/Anizoptera/AzaLibEvent

[![Build Status][TravisImage]][Travis]


Table of Contents
-----------------

1. [Introduction](#introduction)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Examples](#examples)
   * [Polling STDIN using basic API](#example-1---polling-stdin-using-basic-api)
   * [Polling STDIN using buffered event API](#example-2---polling-stdin-using-buffered-event-api)
6. [Credits](#credits)
7. [License](#license)
8. [Links](#links)


Introduction
------------

**Main features:**

* Convenient, fully documented and tested in production API;
* Timers and intervals system (look at `EventBase::timerAdd`);
* Special base reinitializing for forks (look at `EventBase::reinitialize`);
* Error handling with exceptions;
* Automatic resources cleanup;


Requirements
------------

* PHP 5.2.0 (or later);
* [libevent](http://php.net/libevent);


Installation
------------

The recommended way to install AzaLibEvent is [through composer](http://getcomposer.org).
You can see [package information on Packagist][ComposerPackage].

```JSON
{
	"require": {
		"aza/libevent": "~1.0"
	}
}
```


Examples
--------

#### Example #1 - Polling STDIN using basic API

See [examples/basic_api.php](examples/basic_api.php)

```php
/**
 * Callback function to be called when the matching event occurs
 *
 * @param resource $fd     File descriptor
 * @param int      $events What kind of events occurred. See EV_* constants
 * @param array    $args   Event arguments - array(Event $e, mixed $arg)
 */
function print_line($fd, $events, $args)
{
	static $max_requests = 0;
	$max_requests++;

	/**
	 * @var $e    Event
	 * @var $base EventBase
	 */
	list($e, $base) = $args;

	// exit loop after 10 writes
	if ($max_requests == 10) {
		$base->loopExit();
	}

	// print the line
	echo fgets($fd);
}

// Create base
$base = new EventBase;

// Setup and enable event
$ev = new Event();
$ev->set(STDIN, EV_READ|EV_PERSIST, 'print_line', $base)
		->setBase($base)
		->add();

// Start event loop
$base->loop();
```


#### Example #2 - Polling STDIN using buffered event API

See [examples/buffered_api.php](examples/buffered_api.php)

```php
/**
 * Callback to invoke where there is data to read
 *
 * @param resource $buf  File descriptor
 * @param array    $args Event arguments - array(EventBuffer $e, mixed $arg)
 */
function print_line($buf, $args)
{
	static $max_requests;
	$max_requests++;

	/**
	 * @var $e    EventBuffer
	 * @var $base EventBase
	 */
	list($e, $base) = $args;

	// exit loop after 10 writes
	if ($max_requests == 10) {
		$base->loopExit();
	}

	// print the line
	echo $e->read(4096);
}

/**
 * Callback to invoke where there is an error on the descriptor.
 * function(resource $buf, int $what, array $args(EventBuffer $e, mixed $arg))
 *
 * @param resource $buf  File descriptor
 * @param int      $what What kind of error occurred. See EventBuffer::E_* constants
 * @param array    $args Event arguments - array(EventBuffer $e, mixed $arg)
 */
function error_func($buf, $what, $args) {}


// I use Base::getEventBase() to operate always with the
// same instance, but you can simply use "new EventBase()"

// Get event base
$base = Base::getEventBase();

// Create buffered event
$ev = new EventBuffer(STDIN, 'print_line', null, 'error_func', $base);
$ev->setBase($base)->enable(EV_READ);

// Start loop
$base->loop();
```


Credits
-------

AzaLibEvent is a part of [Anizoptera CMF][], written by [Amal Samally][] (amal.samally at gmail.com) and [AzaGroup][] team.


License
-------

Released under the [MIT](LICENSE.md) license.


Links
-----

* [Composer package][ComposerPackage]
* [Last build on the Travis CI][Travis]
* [Project profile on the Ohloh](https://www.ohloh.net/p/AzaLibEvent)
* Other Anizoptera CMF components on the [GitHub][Anizoptera CMF] / [Packagist](https://packagist.org/packages/aza)
* (RU) [AzaGroup team blog][AzaGroup]



[Anizoptera CMF]:  https://github.com/Anizoptera
[Amal Samally]:    http://azagroup.ru/about/#amal
[AzaGroup]:        http://azagroup.ru/
[ComposerPackage]: https://packagist.org/packages/aza/libevent
[TravisImage]:     https://secure.travis-ci.org/Anizoptera/AzaLibEvent.png?branch=master
[Travis]:          http://travis-ci.org/Anizoptera/AzaLibEvent
