<?php

use Aza\Components\LibEvent\EventBase;
use Aza\Components\LibEvent\Event;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Example #1 - polling STDIN using basic API
 *
 * @link http://www.php.net/manual/en/libevent.examples.php
 *
 * @project Anizoptera CMF
 * @package system.libevent
 * @author  Amal Samally <amal.samally at gmail.com>
 * @license MIT
 */

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

/**
 * Timer callback
 *
 * @param string    $timer_name
 * @param mixed     $arg
 * @param int       $iteration
 * @param EventBase $event_base
 *
 * @return bool
 */
function timer($timer_name, $arg, $iteration, $event_base)
{
	echo "timer #$iteration: " . microtime(true) . PHP_EOL;
	return true;
}

/**
 * Signal callback
 *
 * @param int   $signo
 * @param int   $events
 * @param array $arg
 */
function signal($signo, $events, $arg)
{
	echo "signal caught - " . $arg[2] . PHP_EOL;
}


// Create base
$loop = new EventBase;

// Setup and enable event
$ev = new Event();
$ev->set(STDIN, EV_READ|EV_PERSIST, 'print_line', $loop)
	->setBase($loop)
	->add();

// Setup timer
$loop->timerAdd('main', 4, 'timer');

// Setup signal handler
$evS1 = new Event();
$evS1->setSignal(SIGWINCH, 'signal')
	->setBase($loop)
	->add();

posix_kill(posix_getpid(), SIGWINCH);

// Start event loop once for 2 seconds
$loop->loopExit(2)->loop();

// Fork support test
//if (!$pid = $loop->fork()) {
//	exit; // exit in child
//}
//echo "fork" . PHP_EOL;
//pcntl_waitpid($pid, $status, WUNTRACED);

// Setup signal handler
$evS2 = new Event();
$evS2->setSignal(SIGCHLD, 'signal')
	->setBase($loop)
	->add();

posix_kill(posix_getpid(), SIGWINCH);
posix_kill(posix_getpid(), SIGCHLD);

// Start event loop
$loop->loop();
