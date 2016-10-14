<?php

namespace Aza\Components\LibEvent\Tests;
use Aza\Components\LibEvent\EventBase;
use Aza\Components\LibEvent\Event;
use Aza\Components\LibEvent\EventBuffer;
use Aza\Components\Socket\ASocket;
use Aza\Components\Socket\Socket;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Testing LibEvent wrapper
 *
 * @project Anizoptera CMF
 * @package system.libevent
 * @author  Amal Samally <amal.samally at gmail.com>
 * @license MIT
 *
 * @requires extension libevent
 */
class LibEventTest extends TestCase
{
	/**
	 * @var bool
	 */
	protected static $defSockets;


	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		self::$defSockets = Socket::$useSockets;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function tearDownAfterClass()
	{
		// Set value to default
		Socket::$useSockets = self::$defSockets;

		// Clean all event loops
		EventBase::cleanAllLoops();

		// Cleanup
		gc_collect_cycles();
	}



	/**
	 * Tests buffered API
	 *
	 * @author amal
	 * @group unit
	 *
	 * @requires extension sockets
	 */
	public function testBuffered()
	{
		Socket::$useSockets = false;
		$this->processingBuffered();

		Socket::$useSockets = true;
		$this->processingBuffered();
	}

	/**
	 * Tests signals handling
	 *
	 * @author amal
	 * @group unit
	 *
	 * @requires extension pcntl
	 * @requires extension posix
	 */
	public function testSignalsHandling()
	{
		$base = new EventBase();

		$lastCatched = null;
		$cbSignal = function ($signo, $events, $arg) use (&$lastCatched) {
			$lastCatched = $arg[2];
		};

		$signal = SIGCHLD;
		$ev = new Event;
		$ev->setSignal($signal, $cbSignal)->setBase($base)->add();
		posix_kill(posix_getpid(), $signal);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertSame($signal, $lastCatched);
		$lastCatched = null;
		$ev->del()->free();

		$signal = SIGTERM;
		$ev = new Event;
		$ev->setSignal($signal, $cbSignal)->setBase($base)->add();
		posix_kill(posix_getpid(), $signal);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertSame($signal, $lastCatched);
		$lastCatched = null;
		$ev->del()->free();

		$signal = SIGWINCH;
		$ev = new Event;
		$ev->setSignal($signal, $cbSignal)->setBase($base)->add();
		posix_kill(posix_getpid(), $signal);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertSame($signal, $lastCatched);
		$lastCatched = null;
		$ev->del()->free();

		unset($lastCatched);
	}

	/**
	 * Tests signals handling
	 *
	 * @author amal
	 * @group integrational
	 *
	 * @requires extension pcntl
	 * @requires extension posix
	 */
	public function testSignalsHandlingAndFork()
	{
		$base = new EventBase();

		$lastCatched = null;
		$cbSignal = function ($signo, $events, $arg) use (&$lastCatched) {
			$lastCatched = $arg[2];
		};

		$signal = SIGCHLD;
		$ev = new Event;
		$ev->setSignal($signal, $cbSignal)->setBase($base)->add();
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertSame(null, $lastCatched);
		if (!$pid = $base->fork()) {
			exit; // exit in child
		}
		pcntl_waitpid($pid, $status, WUNTRACED);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertSame($signal, $lastCatched);
		posix_kill(posix_getpid(), $signal);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertSame($signal, $lastCatched);
		$lastCatched = null;
		$ev->del()->free();

		$signal = SIGTERM;
		$ev = new Event;
		$ev->setSignal($signal, $cbSignal)->setBase($base)->add();
		if (!$pid = $base->fork()) {
			exit; // exit in child
		}
		pcntl_waitpid($pid, $status, WUNTRACED);
		posix_kill(posix_getpid(), $signal);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertSame($signal, $lastCatched);
		$lastCatched = null;
		$ev->del()->free();

		$signal = SIGWINCH;
		$ev = new Event;
		$ev->setSignal($signal, $cbSignal)->setBase($base)->add();
		if (!$pid = $base->fork()) {
			exit; // exit in child
		}
		pcntl_waitpid($pid, $status, WUNTRACED);
		posix_kill(posix_getpid(), $signal);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertSame($signal, $lastCatched);
		$lastCatched = null;
		$ev->del()->free();

		unset($lastCatched);
	}


	/**
	 * Main API processing
	 */
	protected function processing()
	{
		/**
		 * Prepare
		 *
		 * @var ASocket $sIn
		 * @var ASocket $sOut
		 */
		list($sIn, $sOut) = Socket::pair();

		$data = array(
			1 => $this->getData(),
			2 => $this->getData(),
		);
		$last = null;

		$base = new EventBase();

		$cbRead = function ($fd, $events, $args) use (&$data, &$last) {
			/** @var $e Event */
			list($e, $pipeNumber) = $args;
			if ($events & EV_READ) {
				$data[$pipeNumber]['read']++;
//				$last = Socket::read($fd, 4096);
			} else if ($events & EV_WRITE) {
				$data[$pipeNumber]['write']++;
			}
		};

		$ev1 = new Event;
		$ev1->set($sIn->resource, EV_READ|EV_PERSIST, $cbRead, 1)->setBase($base)->add();

		$ev2 = new Event;
		$ev2->set($sIn->resource, EV_READ|EV_PERSIST, $cbRead, 2)->setBase($base)->add();

		// First check
		$this->assertEquals(array(
			1 => array(
				'read'		=> 0,
				'write'		=> 0,
				'timeout'	=> 0,
				'errors' => array(
					'eof' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'error' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'timeout' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
				),
			),
			2 => array(
				'read'		=> 0,
				'write'		=> 0,
				'timeout'	=> 0,
				'errors' => array(
					'eof' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'error' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'timeout' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
				),
			),
		), $data);
		$base->loop(EVLOOP_NONBLOCK);

		$ex = "something in 1 \r\n";
		$sIn->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertEquals(1, $data[2]['read']);
		$this->assertEquals($ex, $last);

		$ex = "something in 2 \n";
		$sIn->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertEquals(2, $data[2]['read']);
		$this->assertEquals($ex, $last);

		$ex = "something 1 out\r";
		$sOut->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertEquals(1, $data[1]['read']);
		$this->assertEquals($ex, $last);

		$ex = "something out\0";
		$sOut->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertEquals(2, $data[1]['read']);
		$this->assertEquals($ex, $last);

		$ex = str_repeat(mt_rand(123, 98673) . mt_rand(835, 23457) . mt_rand(345, 48665), 456);
		$sOut->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertEquals(3, $data[1]['read']);
		$this->assertEquals($ex, $last);

		$this->assertEquals(array(
			1 => array(
				'read'		=> 3,
				'write'		=> 2,
				'timeout'	=> 0,
				'errors' => array(
					'eof' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'error' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'timeout' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
				),
			),
			2 => array(
				'read'		=> 2,
				'write'		=> 3,
				'timeout'	=> 0,
				'errors' => array(
					'eof' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'error' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'timeout' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
				),
			),
		), $data);

		$sIn->close();
		$sOut->close();

		$base->loop(EVLOOP_NONBLOCK);
		$this->assertEquals(array(
			1 => array(
				'read'    => 3,
				'write'   => 2,
				'timeout' => 0,
				'errors'  => array(
					'eof'     => array(
						'read'  => 0,
						'write' => 0,
					),
					'error'   => array(
						'read'  => 0,
						'write' => 0,
					),
					'timeout' => array(
						'read'  => 0,
						'write' => 0,
					),
				),
			),
			2 => array(
				'read'    => 2,
				'write'   => 3,
				'timeout' => 0,
				'errors'  => array(
					'eof'     => array(
						'read'  => 0,
						'write' => 0,
					),
					'error'   => array(
						'read'  => 0,
						'write' => 0,
					),
					'timeout' => array(
						'read'  => 0,
						'write' => 0,
					),
				),
			),
		), $data);

		$base->free();
	}

	/**
	 * Buffered API processing
	 */
	protected function processingBuffered()
	{
		/**
		 * Prepare
		 *
		 * @var ASocket $sIn
		 * @var ASocket $sOut
		 */
		list($sIn, $sOut) = Socket::pair();

		$data = array(
			1 => $this->getData(),
			2 => $this->getData(),
		);
		$last = null;

		$base = new EventBase();

		$cbRead  = function ($buf, $args) use (&$data, &$last)
		{
			/** @var $e EventBuffer */
			list($e, $pipeNumber) = $args;
			$data[$pipeNumber]['read']++;
			$last = $e->read(4096);
		};
		$cbWrite = function ($buf, $args) use (&$data)
		{
			$pipeNumber = $args[1];
			$data[$pipeNumber]['write']++;
		};
		$cbError = function ($buf, $what, $args) use (&$data)
		{
			$pipeNumber = $args[1];
			$n = 'errors';
			if ($what & EventBuffer::E_READ) {
				$names = array();
				if ($what & EventBuffer::E_EOF) {
					$names[] = 'eof';
				}
				if ($what & EventBuffer::E_ERROR) {
					$names[] = 'error';
				}
				if ($what & EventBuffer::E_TIMEOUT) {
					$names[] = 'timeout';
				}
				foreach ($names as $name) {
					$data[$pipeNumber][$n][$name]['read']++;
				}
			}
			if ($what & EventBuffer::E_WRITE) {
				$names = array();
				if ($what & EventBuffer::E_EOF) {
					$names[] = 'eof';
				}
				if ($what & EventBuffer::E_ERROR) {
					$names[] = 'error';
				}
				if ($what & EventBuffer::E_TIMEOUT) {
					$names[] = 'timeout';
				}
				foreach ($names as $name) {
					$data[$pipeNumber][$n][$name]['read']++;
				}
			}
			dp("error ($pipeNumber - $what)");
		};

		$ev1 = new EventBuffer($sIn->resource, $cbRead, $cbWrite, $cbError, 1);
		$ev1->setBase($base)->setWatermark(EV_WRITE | EV_READ)->setPriority()->setTimout()->enable(EV_WRITE | EV_READ);

		$ev2 = new EventBuffer($sOut->resource, $cbRead, $cbWrite, $cbError, 2);
		$ev2->setBase($base)->setWatermark(EV_WRITE | EV_READ)->setPriority()->setTimout()->enable(EV_WRITE | EV_READ);


		// First check
		$this->assertEquals(array(
			1 => array(
				'read'		=> 0,
				'write'		=> 0,
				'timeout'	=> 0,
				'errors' => array(
					'eof' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'error' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'timeout' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
				),
			),
			2 => array(
				'read'		=> 0,
				'write'		=> 0,
				'timeout'	=> 0,
				'errors' => array(
					'eof' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'error' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
					'timeout' => array(
						'read'	=> 0,
						'write'	=> 0,
					),
				),
			),
		), $data);

		// Write is allowed
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertEquals(1, $data[1]['write']);
		$this->assertEquals(1, $data[2]['write']);

		$ex = "something in 1 \r\n";
		$sIn->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertTrue($data[2]['read'] >= 1);
		$this->assertEquals($ex, $last);

		$ex = "something in 2 \n";
		$ev1->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertTrue($data[2]['read'] >= 2);
		$this->assertEquals($ex, $last);

		$ex = "something 1 out\r";
		$sOut->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertTrue($data[1]['read'] >= 1);
		$this->assertEquals($ex, $last);

		$ex = "something out\0";
		$ev2->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertTrue($data[1]['read'] >= 2);
		$this->assertEquals($ex, $last);

		$ex = str_repeat(mt_rand(123, 98673) . mt_rand(835, 23457) . mt_rand(345, 48665), 456);
		$ev2->write($ex);
		$base->loop(EVLOOP_NONBLOCK);
		$this->assertTrue($data[1]['read'] >= 3);
		$this->assertEquals($ex, $last);

		$data[1]['read'] = null;
		$this->assertEquals(array(
			1 => array(
				'read'    => null,
				'write'   => 2,
				'timeout' => 0,
				'errors'  => array(
					'eof'     => array(
						'read'  => 0,
						'write' => 0,
					),
					'error'   => array(
						'read'  => 0,
						'write' => 0,
					),
					'timeout' => array(
						'read'  => 0,
						'write' => 0,
					),
				),
			),
			2 => array(
				'read'    => 2,
				'write'   => 3,
				'timeout' => 0,
				'errors'  => array(
					'eof'     => array(
						'read'  => 0,
						'write' => 0,
					),
					'error'   => array(
						'read'  => 0,
						'write' => 0,
					),
					'timeout' => array(
						'read'  => 0,
						'write' => 0,
					),
				),
			),
		), $data);

		$sIn->close();
		$sOut->close();

		$base->loop(EVLOOP_NONBLOCK);
		$data[1]['read'] = null;
		$this->assertEquals(array(
			1 => array(
				'read'    => null,
				'write'   => 2,
				'timeout' => 0,
				'errors'  => array(
					'eof'     => array(
						'read'  => 0,
						'write' => 0,
					),
					'error'   => array(
						'read'  => 0,
						'write' => 0,
					),
					'timeout' => array(
						'read'  => 0,
						'write' => 0,
					),
				),
			),
			2 => array(
				'read'    => 2,
				'write'   => 3,
				'timeout' => 0,
				'errors'  => array(
					'eof'     => array(
						'read'  => 0,
						'write' => 0,
					),
					'error'   => array(
						'read'  => 0,
						'write' => 0,
					),
					'timeout' => array(
						'read'  => 0,
						'write' => 0,
					),
				),
			),
		), $data);

		$base->free();
	}


	/**
	 * Data stub
	 *
	 * @return array
	 */
	protected function getData()
	{
		$data = array(
			'read'    => 0,
			'write'   => 0,
			'timeout' => 0,
			'errors'  => array(
				'eof'     => array(
					'read'  => 0,
					'write' => 0,
				),
				'error'   => array(
					'read'  => 0,
					'write' => 0,
				),
				'timeout' => array(
					'read'  => 0,
					'write' => 0,
				),
			),
		);
		return $data;
	}
}
