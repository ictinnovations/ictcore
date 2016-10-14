<?php

use Aza\Components\LibEvent\EventBase;
use Aza\Components\LibEvent\EventBuffer;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Example #2 - polling STDIN using buffered event API
 *
 * @link http://www.php.net/manual/en/libevent.examples.php
 *
 * @project Anizoptera CMF
 * @package system.libevent
 * @author  Amal Samally <amal.samally at gmail.com>
 * @license MIT
 */


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


// I use EventBase::getMainLoop() here to operate always with the
// same instance, but you can simply use "new EventBase()"

// Get event base
$loop = EventBase::getMainLoop();

// Create buffered event
$ev = new EventBuffer(STDIN, 'print_line', null, 'error_func', $loop);
$ev->setBase($loop)->enable(EV_READ);

// Start event loop once
$loop->loop(EVLOOP_NONBLOCK);

// Fork support test
//if (!$pid = $loop->fork()) {
//	exit; // exit in child
//}
//pcntl_waitpid($pid, $status, WUNTRACED);

// Start event loop
$loop->loop();
