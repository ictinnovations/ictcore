<?php

namespace Aza\Components\LibEvent;
use Aza\Components\LibEvent\Exceptions\Exception;

/**
 * LibEvent "basic" event functionality
 *
 * @link http://www.wangafu.net/~nickm/libevent-book/
 *
 * @uses libevent
 *
 * @project Anizoptera CMF
 * @package system.libevent
 * @author  Amal Samally <amal.samally at gmail.com>
 * @license MIT
 */
abstract class EventBasic
{
	/**
	 * Unique event IDs counter
	 *
	 * @var int
	 */
	private static $counter = 0;

	/**
	 * Unique (for current process) event ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Event resource
	 *
	 * @var resource
	 */
	protected $resource;

	/**
	 * Event loop
	 *
	 * @var EventBase
	 */
	protected $base;


	/**
	 * Creates a new event resource.
	 *
	 * @throws Exception <p>
	 * If Libevent isn't available or can't create
	 * new event resource.
	 * </p>
	 */
	public function __construct()
	{
		if (!EventBase::$hasLibevent) {
			throw new Exception(
				'You need to install PECL extension "Libevent" to use this class'
			);
		}
		$this->id = ++self::$counter;
	}


	/**
	 * Desctructor
	 */
	public function __destruct()
	{
		$this->resource && $this->free();
	}

	/**
	 * Destroys the event and frees all the resources associated.
	 *
	 * @param bool $afterForkCleanup [optional] <p>
	 * Special handling of cleanup after fork
	 * </p>
	 *
	 * @return $this
	 */
	public function free($afterForkCleanup = false)
	{
		if ($this->base) {
			unset($this->base->events[$this->id]);
			$this->base = null;
		}
		return $this;
	}


	/**
	 * Associate event with an event base
	 *
	 * @param EventBase $event_base
	 *
	 * @return $this
	 */
	public function setBase($event_base)
	{
		$this->base = $event_base;
		$event_base->events[$this->id] = $this;
		return $this;
	}


	/**
	 * Checks event resource.
	 *
	 * @throws Exception if resource is already freed
	 */
	protected function checkResource()
	{
		if (!$this->resource) {
			throw new Exception(
				"Can't use event resource. It's already freed."
			);
		}
	}
}
