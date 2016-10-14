<?php

namespace Aza\Components\LibEvent;
use Aza\Components\CliBase\Base;
use Aza\Components\LibEvent\Exceptions\Exception;

/**
 * LibEvent base resource wrapper (event loop)
 *
 * @link http://php.net/libevent
 * @link http://www.wangafu.net/~nickm/libevent-book/
 *
 * @uses libevent
 *
 * @project Anizoptera CMF
 * @package system.libevent
 * @author  Amal Samally <amal.samally at gmail.com>
 * @license MIT
 */
class EventBase
{
	/**
	 * Default priority
	 *
	 * @see priorityInit
	 */
	const MAX_PRIORITY = 30;


	/**
	 * Whether Libevent supported
	 *
	 * @var bool
	 */
	public static $hasLibevent = false;

	/**
	 * Unique base IDs counter
	 *
	 * @var int
	 */
	private static $counter = 0;

	/**
	 * Event loops pool [id => loop]
	 *
	 * @var self[]
	 */
	protected static $loops = array();

	/**
	 * Main event loop
	 *
	 * @var self|null
	 */
	protected static $mainLoop;


	/**
	 * Unique base ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Event base resource
	 *
	 * @var resource
	 */
	public $resource;

	/**
	 * Events
	 *
	 * @var Event[]|EventBuffer[]
	 */
	public $events = array();

	/**
	 * Array of timers settings
	 *
	 * @var array[]
	 */
	protected $timers = array();

	/**
	 * Started loop flag
	 */
	protected $inLoop = false;



	/**
	 * Returns global shared event loop
	 *
	 * @param bool $create [optional] <p>
	 * Create new event loop if there is no initialized one.
	 * </p>
	 *
	 * @return self|null
	 */
	public static function getMainLoop($create = true)
	{
		return self::$mainLoop ?: ($create
				? (self::$mainLoop = new EventBase())
				: null);
	}

	/**
	 * Sets or replaces global shared event loop
	 *
	 * @param self $loop
	 */
	public static function setMainLoop($loop)
	{
		self::$mainLoop = $loop;
	}

	/**
	 * Cleans global shared event loop
	 */
	public static function cleanMainLoop()
	{
		self::$mainLoop && self::$mainLoop->free();
		self::$mainLoop = null;
	}

	/**
	 * Cleans all event loops
	 */
	public static function cleanAllLoops()
	{
		$loops = self::$loops;
		foreach ($loops as $loop) {
			$loop->free();
		}
		self::cleanMainLoop();
		self::$loops = array();
	}



	/**
	 * Initializes instance
	 *
	 * @see event_base_new
	 *
	 * @throws Exception
	 *
	 * @param bool $initPriority Whether to init
	 * priority with default value
	 */
	public function __construct($initPriority = true)
	{
		$this->init($initPriority);
	}

	/**
	 * Create and initialize new event base
	 *
	 * @see event_base_new
	 *
	 * @throws Exception
	 *
	 * @param bool $initPriority Whether to init priority with default value
	 *
	 * @return $this
	 */
	protected function init($initPriority = true)
	{
		if (!self::$hasLibevent) {
			throw new Exception(
				'You need to install PECL extension "Libevent" to use this class'
			);
		} else if (!$this->resource = event_base_new()) {
			throw new Exception(
				"Can't create event base resource (event_base_new)"
			);
		}

		$initPriority
			&& $this->priorityInit();

		self::$loops[
			$this->id = ++self::$counter
		] = $this;

		return $this;
	}


	/**
	 * Forks the currently running process
	 *
	 * @see Base::fork
	 *
	 * @return int the PID of the child process is returned
	 * in the parent's thread of execution, and a 0 is
	 * returned in the child's thread of execution.
	 *
	 * @throws Exception if could not fork
	 */
	public function fork()
	{
		// Prepare loops
		$loops = &self::$loops;
		foreach ($loops as $loop) {
			$loop->beforeFork();
		}

		$pid = Base::fork(function() use ($loops) {
			foreach ($loops as $loop) {
				$loop->afterFork();
			}
		});

		// Child
		if (0 === $pid) {
			unset($loops[$this->id]);
			foreach ($loops as $loop) {
				$loop->free();
			}
			$this->reinitialize();
			self::setMainLoop($this);
		}

		return $pid;
	}

	/**
	 * Prepares event base for forking
	 * to avoid resources damage
	 *
	 * Use this method before fork in parent!
	 */
	protected function beforeFork()
	{
		// Trigger already ready events
		$this->inLoop || event_base_loop(
			$this->resource, EVLOOP_NONBLOCK
		);

		if (function_exists('event_base_reinit')) {
			// Libevent >= 1.4.3-alpha
		}
		else if ($this->events) {
			foreach ($this->events as $e) {
				// Disable buffered events
				if ($e instanceof EventBuffer) {
					$e->disable(0);
				}
				// Remove simple events
				else {
					$e->beforeFork();
				}
			}
		}
	}

	/**
	 * Enables all disabled events
	 *
	 * Use this method after fork in parent!
	 *
	 * @internal Public for PHP 5.3
	 *
	 * @see event_base_reinit
	 */
	public function afterFork()
	{
		if (function_exists('event_base_reinit')) {
			// Libevent >= 1.4.3-alpha
			event_base_reinit($this->resource);
		}
		else if ($this->events) {
			foreach ($this->events as $e) {
				// Enable buffered events
				if ($e instanceof EventBuffer) {
					$e->enable(0);
				}
				// Readd simple events
				else {
					$e->afterFork();
				}
			}
		}
	}

	/**
	 * Cleans all attached events and reinitializes
	 * event base.
	 *
	 * Use this method after fork in child!
	 *
	 * @see event_base_reinit
	 *
	 * @param bool $initPriority
	 * Whether to init priority with default value
	 */
	protected function reinitialize($initPriority = true)
	{
		if (function_exists('event_base_reinit')) {
			// Libevent >= 1.4.3-alpha
			$this->freeAttachedEvents(true);
			event_base_reinit($this->resource);
		} else {
			$this->free(true)->init($initPriority);
		}
	}


	/**
	 * Desctructor
	 */
	public function __destruct()
	{
		$this->free();
	}

	/**
	 * Destroys the specified event_base and frees
	 * all the resources associated.
	 *
	 * Note that it's not possible to destroy an
	 * event base with events attached to it.
	 *
	 * @see event_base_free
	 *
	 * @param bool $afterForkCleanup [optional] <p>
	 * Special handling of cleanup after fork
	 * </p>
	 *
	 * @return $this
	 */
	public function free($afterForkCleanup = false)
	{
		$this->freeAttachedEvents($afterForkCleanup);
		if ($this->resource) {
			@event_base_free($this->resource);
			$this->resource = null;
		}
		unset(self::$loops[$this->id]);
		return $this;
	}

	/**
	 * Frees all attached timers and events
	 *
	 * @param bool $afterForkCleanup [optional] <p>
	 * Special handling of cleanup after fork
	 * </p>
	 *
	 * @return $this
	 */
	public function freeAttachedEvents($afterForkCleanup = false)
	{
		if ($this->events) {
			foreach ($this->events as $e) {
				$e->free($afterForkCleanup);
			}
		}
		$this->events = $this->timers = array();
		return $this;
	}


	/**
	 * Associate event base with an event (or buffered event).
	 *
	 * @see Event::setBase
	 * @see EventBuffer::setBase
	 *
	 * @throws Exception
	 *
	 * @param EventBasic $event
	 *
	 * @return $this
	 */
	public function setEvent($event)
	{
		$event->setBase($this);
		return $this;
	}


	/**
	 * Starts event loop for the specified event base.
	 *
	 * @see event_base_loop
	 *
	 * @throws Exception if error
	 *
	 * @param int $flags [optional] <p>
	 * Any combination of EVLOOP_ONCE
	 * and EVLOOP_NONBLOCK.
	 * <p>
	 *
	 * @return int Returns 0 on success, 1 if no events were registered.
	 */
	public function loop($flags = 0)
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		// Reentrant invocation protection
		if ($this->inLoop) {
			$this->loopBreak();
			throw new Exception(
				"Reentrant invocation ($resource). "
				."Only one event_base_loop can run on each event_base at once."
			);
		}

		$this->inLoop = true;
		if (-1 === $res = event_base_loop($resource, $flags)) {
			$this->inLoop = false;
			throw new Exception(
				"Can't start base loop (event_base_loop)"
			);
		}
		$this->inLoop = false;

		return $res;
	}

	/**
	 * Abort the active event loop immediately.
	 * The behaviour is similar to break statement.
	 *
	 * @see event_base_loopbreak
	 *
	 * @throws Exception
	 *
	 * @return $this
	 */
	public function loopBreak()
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		if (!event_base_loopbreak($resource)) {
			throw new Exception(
				"Can't break loop (event_base_loopbreak)"
			);
		}
		return $this;
	}

	/**
	 * Exit loop after a time.
	 *
	 * @see event_base_loopexit
	 *
	 * @throws Exception
	 *
	 * @param int $timeout [optional] <p>
	 * Timeout in microseconds.
	 * <p>
	 *
	 * @return $this
	 */
	public function loopExit($timeout = -1)
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		if (!event_base_loopexit($resource, $timeout)) {
			throw new Exception(
				"Can't set loop exit timeout (event_base_loopexit)"
			);
		}
		return $this;
	}


	/**
	 * Sets the maximum priority level of the event base.
	 *
	 * @see event_base_priority_init
	 *
	 * @throws Exception
	 *
	 * @param int $value
	 *
	 * @return $this
	 */
	public function priorityInit($value = self::MAX_PRIORITY)
	{
		$this->checkResource();
		if (!event_base_priority_init($this->resource, ++$value)) {
			throw new Exception(
				"Can't set the maximum priority level of the event base"
				." to {$value} (event_base_priority_init)"
			);
		}
		return $this;
	}


	/**
	 * Checks event base resource.
	 *
	 * @throws Exception if resource is already freed
	 */
	public function checkResource()
	{
		if (!$this->resource) {
			throw new Exception(
				"Can't use event base resource. It's already freed."
			);
		}
	}

	/**
	 * Returns if loop is started
	 *
	 * @return bool
	 */
	public function getIsInLoop()
	{
		return $this->inLoop;
	}


	/**
	 * Adds a new named timer to the base or customize existing
	 *
	 * @param string $name <p>
	 * Timer name
	 * </p>
	 * @param int  $interval <p>
	 * Interval. In seconds by default (47 seconds max!). See <b>$q</b>
	 * argument for details.</p>
	 * <p>On Linux kernels at least up to 2.6.24.4, epoll can't handle timeout
	 * values bigger than (LONG_MAX - 999ULL)/HZ. HZ in the wild can be
	 * as big as 1000, and LONG_MAX can be as small as (1<<31)-1, so the
	 * largest number of msec we can support here is 2147482. Let's
	 * round that down by 47 seconds.
	 * </p>
	 * @param callback $callback <p>
	 * Callback function to be called when the interval expires.<br/>
	 * <tt>function(string $timer_name, mixed $arg,
	 * int $iteration, EventBase $event_base){}</tt><br/>
	 * If callback will return TRUE timer will be started
	 * again for next iteration.
	 * </p>
	 * @param mixed $arg <p>
	 * Additional timer argument
	 * </p>
	 * @param bool $start <p>
	 * Whether to start timer
	 * </p>
	 * @param int $q <p>
	 * Interval multiply factor
	 * </p>
	 *
	 * @throws Exception
	 */
	public function timerAdd($name, $interval = null, $callback = null,
		$arg = null, $start = true, $q = 1000000)
	{
		$notExists = !isset($this->timers[$name]);

		if (($notExists || $callback)
		    && !is_callable($callback, false, $callableName)
		) {
			throw new Exception(
				"Incorrect callback '{$callableName}' for timer ({$name})."
			);
		}

		if ($notExists) {
			$event = new Event();
			$event->setTimer(array($this, '_onTimer'), $name)
					->setBase($this)
					->setPriority(self::MAX_PRIORITY);

			$this->timers[$name] = array(
				'name'     => $name,
				'callback' => $callback,
				'event'    => $event,
				'interval' => $interval,
				'arg'      => $arg,
				'q'        => $q,
				'i'        => 0,
			);
		} else {
			/** @var $timer Event[]|mixed[] */
			$timer = &$this->timers[$name];
			$timer['event']->del();

			$callback
				&& $timer['callback'] = $callback;

			$interval > 0
				&& $timer['interval'] = $interval;

			isset($arg)
				&& $timer['arg'] = $arg;

			$timer['i'] = 0;
		}

		if ($start) {
			$this->timerStart($name);
		}
	}

	/**
	 * Starts timer
	 *
	 * @see timerAdd
	 *
	 * @param string $name <p>
	 * Timer name
	 * </p>
	 * @param int $interval [optional] <p>
	 * Interval. In seconds by default
	 * </p>
	 * @param mixed $arg [optional] <p>
	 * Additional timer argument
	 * </p>
	 * @param bool $resetIteration [optional] <p>
	 * Whether to reset iteration counter
	 * </p>
	 *
	 * @throws Exception
	 */
	public function timerStart($name, $interval = null,
		$arg = null, $resetIteration = true)
	{
		if (!isset($this->timers[$name])) {
			throw new Exception(
				"Unknown timer '{$name}'. Add timer before using."
			);
		}

		/** @var $timer Event[]|mixed[] */
		$timer = &$this->timers[$name];

		$resetIteration
			&& $timer['i'] = 0;

		isset($arg)
			&& $timer['arg'] = $arg;

		$interval > 0
			&& $timer['interval'] = $interval;

		$timer['event']->add((int)($timer['interval'] * $timer['q']));
	}

	/**
	 * <p>Stops timer when it's started and waiting.</p>
	 *
	 * <p>Don't call from timer callback.
	 * Return FALSE instead - see {@link timerAdd}().</p>
	 *
	 * @see timerAdd
	 *
	 * @param string $name Timer name
	 */
	public function timerStop($name)
	{
		if (!isset($this->timers[$name])) {
			return;
		}

		/** @var $timer Event[]|mixed[] */
		$timer = &$this->timers[$name];
		$timer['event']->del();
		$timer['i'] = 0;
	}

	/**
	 * Completely destroys timer
	 *
	 * @see timerAdd
	 *
	 * @param string $name Timer name
	 */
	public function timerDelete($name)
	{
		if (!isset($this->timers[$name])) {
			return;
		}
		/** @var $timer Event[] */
		$timer = $this->timers[$name];
		$timer['event']->free();
		unset($this->timers[$name]);
	}

	/**
	 * Return whther timer with such name exists in the base
	 *
	 * @see timerAdd
	 *
	 * @param string $name Timer name
	 *
	 * @return bool
	 */
	public function timerExists($name)
	{
		return isset($this->timers[$name]);
	}

	/**
	 * Timer callback
	 *
	 * @see Event::setTimer
	 *
	 * @internal
	 *
	 * @param null  $fd
	 * @param int   $event EV_TIMEOUT
	 * @param array $args
	 */
	public function _onTimer($fd, $event, $args)
	{
		// Skip deleted timers
		if (!isset($this->timers[$name = $args[1]])) {
			return;
		}

		// Invoke callback
		$timer = &$this->timers[$name];
		if (call_user_func(
			$timer['callback'],
			$name,
			$timer['arg'],
			++$timer['i'],
			$this,
			$event,
			$fd
		)) {
			$this->timerStart(
				$name, null, null, false
			);
		} else {
			$timer['i'] = 0;
		}
	}
}

// libevent extension
EventBase::$hasLibevent = extension_loaded('libevent');
