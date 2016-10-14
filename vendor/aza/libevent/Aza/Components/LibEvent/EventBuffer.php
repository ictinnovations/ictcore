<?php

namespace Aza\Components\LibEvent;
use Aza\Components\LibEvent\Exceptions\Exception;

/**
 * LibEvent buffered event resource wrapper
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
class EventBuffer extends EventBasic
{
	/**
	 * Buffer read error
	 */
	const E_READ = 0x01; // EVBUFFER_READ

	/**
	 * Buffer write error
	 */
	const E_WRITE = 0x02; // EVBUFFER_WRITE

	/**
	 * Buffer EOF error
	 */
	const E_EOF = 0x10; // EVBUFFER_EOF

	/**
	 * Buffer error
	 */
	const E_ERROR = 0x20; // EVBUFFER_ERROR

	/**
	 * Buffer timeout error
	 */
	const E_TIMEOUT = 0x40; // EVBUFFER_TIMEOUT


	/**
	 * Default <i>lowmark</i> in bytes
	 *
	 * @see setWatermark
	 */
	const DEF_LOWMARK = 1;

	/**
	 * Default <i>highmark</i> in bytes
	 *
	 * @see setWatermark
	 */
	const DEF_HIGHMARK = 0xffffff;

	/**
	 * Default priority
	 *
	 * @see setPriority
	 */
	const DEF_PRIORITY = 10;

	/**
	 * Default read timeout
	 *
	 * @see setTimout
	 */
	const DEF_TIMEOUT_READ = 30;

	/**
	 * Default write timeout
	 *
	 * @see setTimout
	 */
	const DEF_TIMEOUT_WRITE = 30;

	/**
	 * Default max single read size.
	 * 64kb by default.
	 *
	 * @see setMaxSingleReadSize
	 */
	const DEF_READ_SIZE = 0x10000;

	/**
	 * Default max single write size
	 * 64kb by default.
	 *
	 * @see setMaxSingleWriteSize
	 */
	const DEF_WRITE_SIZE = 0x10000;


	/**
	 * Last enabled events
	 *
	 * @see enable
	 *
	 * @var int|null
	 */
	protected $events;

	/**
	 * Stream
	 *
	 * @var resource
	 */
	protected $stream;

	/**
	 * Last read timeout
	 *
	 * @see setTimout
	 *
	 * @var int|null
	 */
	protected $read_timeout;

	/**
	 * Last write timeout
	 *
	 * @see setTimout
	 *
	 * @var int|null
	 */
	protected $write_timeout;

	/**
	 * Last marks events
	 *
	 * @see setWatermark
	 *
	 * @var int|null
	 */
	protected $mark_events;

	/**
	 * Last lowmark
	 *
	 * @see setWatermark
	 *
	 * @var int|null
	 */
	protected $lowmark;

	/**
	 * Last highmark
	 *
	 * @see setWatermark
	 *
	 * @var int|null
	 */
	protected $highmark;

	/**
	 * Last priority
	 *
	 * @see setPriority
	 *
	 * @var int|null
	 */
	protected $priority;

	/**
	 * Read callback
	 *
	 * @var callable|null
	 */
	protected $readcb;

	/**
	 * Write callback
	 *
	 * @var callable|null
	 */
	protected $writecb;

	/**
	 * Error callback
	 *
	 * @var callable
	 */
	protected $errorcb;

	/**
	 * Callbacks argument
	 *
	 * @var mixed
	 */
	protected $arg;



	/**
	 * Creates a new buffered event resource.
	 *
	 * @see event_buffer_new
	 *
	 * @throws Exception
	 *
	 * @param resource $stream <p>
	 * Valid PHP stream resource.
	 * Must be castable to file descriptor.
	 * </p>
	 * @param callback|null $readcb <p>
	 * Callback to invoke where there is data to read,
	 * or NULL if no callback is desired.
	 * <br><tt>function(resource $buf,
	 * array $args(EventBuffer $e, mixed $arg))</tt>
	 * </p>
	 * @param callback|null $writecb <p>
	 * Callback to invoke where the descriptor is ready
	 * for writing, or NULL if no callback is desired.
	 * <br><tt>function(resource $buf,
	 * array $args(EventBuffer $e, mixed $arg))</tt>
	 * </p>
	 * @param callback $errorcb <p>
	 * Callback to invoke where there is an error
	 * on the descriptor, cannot be NULL.
	 * <br><tt>function(resource $buf, int $what,
	 * array $args(EventBuffer $e, mixed $arg))</tt>
	 * </p>
	 * @param mixed $arg [optional] <p>
	 * An argument that will be passed
	 * to each of the callbacks.
	 * </p>
	 */
	public function __construct($stream, $readcb,
		$writecb, $errorcb, $arg = null)
	{
		parent::__construct();
		$this->init(
			$stream, $readcb, $writecb, $errorcb, $arg
		);
	}

	/**
	 * Helper initialization method
	 *
	 * @throws Exception
	 *
	 * @param resource $stream
	 * @param callback|null $readcb
	 * @param callback|null $writecb
	 * @param callback $errorcb
	 * @param mixed $arg [optional]
	 */
	protected function init($stream, $readcb,
		$writecb, $errorcb, $arg = null)
	{
		if (!$this->resource = event_buffer_new(
			$stream, $readcb, $writecb, $errorcb,
			array($this, $arg)
		)) {
			throw new Exception(
				"Can't create new buffered event resource (event_buffer_new)"
			);
		}
		$this->stream  = $stream;
		$this->readcb  = $readcb;
		$this->writecb = $writecb;
		$this->errorcb = $errorcb;
		$this->arg     = $arg;
	}

	/**
	 * Full event reinitialization
	 *
	 * @param bool $enable Enable event
	 */
	public function reinit($enable = true)
	{
		event_buffer_free($this->resource);

		$this->init(
			$this->stream,
			$this->readcb,
			$this->writecb,
			$this->errorcb,
			$this->arg
		);
		$this->setBase($this->base);
		if ($this->mark_events) {
			$this->setWatermark(
				$this->mark_events,
				$this->lowmark,
				$this->highmark
			);
		}
		if ($this->priority) {
			$this->setPriority($this->priority);
		}
		if ($this->read_timeout || $this->write_timeout) {
			$this->setTimout(
				$this->read_timeout,
				$this->write_timeout
			);
		}
		if ($enable) {
			$this->enable($this->events);
		}
	}


	/**
	 * Disables buffered event
	 *
	 * @see event_buffer_disable
	 *
	 * @throws Exception
	 *
	 * @param int|bool $events
	 * Any combination of EV_READ and EV_WRITE.
	 *
	 * @return $this
	 */
	public function disable($events = EV_READ)
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		$events || $events = $this->events;
		if (!event_buffer_disable($resource, $events)) {
			// Trigger already ready events
			$this->base->loop(EVLOOP_NONBLOCK);

			// Try again
			if (!event_buffer_disable($resource, $events)) {
				throw new Exception(
					"Can't disable buffered event (event_buffer_disable)"
				);
			}
		}
		return $this;
	}

	/**
	 * Enables buffered event
	 *
	 * @see event_buffer_enable
	 *
	 * @throws Exception
	 *
	 * @param int $events Any combination of EV_READ and EV_WRITE.
	 *
	 * @return $this
	 */
	public function enable($events = EV_READ)
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		$events || $events = $this->events;
		if (!event_buffer_enable($resource, $events)) {
			// Resource is damaged, so reinit
			$this->reinit(false);

			// Try again
			if (!event_buffer_enable($this->resource, $events)) {
				throw new Exception(
					"Can't enable buffered event (event_buffer_enable)"
				);
			}
		}
		$this->events = $events;
		return $this;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @see event_buffer_base_set
	 *
	 * @throws Exception
	 */
	public function setBase($event_base)
	{
		$this->checkResource();
		$event_base->checkResource();
		if (!event_buffer_base_set(
			$this->resource, $event_base->resource
		)) {
			throw new Exception(
				"Can't set buffered event base (event_buffer_base_set)"
			);
		}
		return parent::setBase($event_base);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see event_buffer_free
	 */
	public function free($afterForkCleanup = false)
	{
		parent::free();
		// We need to use it carefully, cause it can
		// damage resource in the parent process
		if ($resource = $this->resource) {
			event_buffer_disable($resource, EV_READ|EV_WRITE);
			event_buffer_free($resource);
			$this->resource = null;
			$this->stream   = null;
			$this->readcb   = null;
			$this->writecb  = null;
			$this->errorcb  = null;
			$this->arg      = null;
		}
		return $this;
	}


	/**
	 * Reads data from the input buffer of the buffered event.
	 *
	 * @see event_buffer_read
	 *
	 * @param int $data_size Data size in bytes
	 *
	 * @return string|bool Data from buffer or FALSE
	 */
	public function read($data_size)
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		return event_buffer_read($resource, $data_size);
	}

	/**
	 * Writes data to the specified buffered event.
	 *
	 * @see event_buffer_write
	 *
	 * @throws Exception
	 *
	 * @param string $data <p>
	 * The data to be written.
	 * </p>
	 * @param int $data_size [optional] <p>
	 * Optional size parameter. Writes all the data by default
	 * </p>
	 *
	 * @return $this
	 */
	public function write($data, $data_size = -1)
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		// Write data
		if (!event_buffer_write($resource, $data, $data_size)) {
			throw new Exception(
				"Can't write data to the buffered event (event_buffer_write)"
			);
		}
		return $this;
	}


	/**
	 * Reads all available data from buffer
	 *
	 * @param int $read_portion_size Read portion size in bytes
	 *
	 * @return string Data from buffer
	 *
	 * @TODO: Limit max size
	 */
	public function readAll($read_portion_size)
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		$buf = '';
		while ('' != $str = event_buffer_read($resource, $read_portion_size)) {
			$buf .= $str;
		}

		return $buf;
	}

	/**
	 * Reads all available data from buffer and cleans it
	 *
	 * @return $this
	 */
	public function readAllClean()
	{
		// Save one method call (checkResource)
		($resource = $this->resource) || $this->checkResource();

		while ('' != event_buffer_read($resource, 0x80000));

		return $this;
	}


	/**
	 * Changes the stream on which the buffered event operates.
	 *
	 * @see event_buffer_fd_set
	 *
	 * @throws Exception
	 *
	 * @param resource $stream <p>
	 * Valid PHP stream, must be castable to file descriptor.
	 * </p>
	 *
	 * @return $this
	 */
	public function setStream($stream)
	{
		$this->checkResource();
		if (!event_buffer_fd_set($this->resource, $stream)) {
			throw new Exception(
				"Can't set buffered event stream (event_buffer_fd_set)"
			);
		}
		$this->stream = $stream;
		return $this;
	}

	/**
	 * Sets or changes existing callbacks for the buffered event.
	 *
	 * @see event_buffer_set_callback
	 *
	 * @throws Exception
	 *
	 * @param callback|null $readcb <p>
	 * Callback to invoke where there is data to read,
	 * or NULL if no callback is desired.
	 * <br><tt>function(resource $buf,
	 * array $args(EventBuffer $e, mixed $arg))</tt>
	 * </p>
	 * @param callback|null $writecb <p>
	 * Callback to invoke where the descriptor is ready
	 * for writing, or NULL if no callback is desired.
	 * <br><tt>function(resource $buf,
	 * array $args(EventBuffer $e, mixed $arg))</tt>
	 * </p>
	 * @param callback $errorcb <p>
	 * Callback to invoke where there is an error on
	 * the descriptor, cannot be NULL.
	 * <br><tt>function(resource $buf, int $what,
	 * array $args(EventBuffer $e, mixed $arg))</tt>
	 * </p>
	 * @param mixed $arg [optional] <p>
	 * An argument that will be passed
	 * to each of the callbacks.
	 * </p>
	 *
	 * @return $this
	 */
	public function setCallback($readcb, $writecb, $errorcb, $arg = null)
	{
		$this->checkResource();
		if (!event_buffer_set_callback(
			$this->resource, $readcb, $writecb, $errorcb,
			array($this, $arg)
		)) {
			throw new Exception(
				"Can't set buffered event callbacks (event_buffer_set_callback)"
			);
		}
		$this->readcb  = $readcb;
		$this->writecb = $writecb;
		$this->errorcb = $errorcb;
		$this->arg     = $arg;
		return $this;
	}


	/**
	 * Sets the read and write timeouts for the specified buffered event.
	 *
	 * @see event_buffer_timeout_set
	 *
	 * @throws Exception
	 *
	 * @param int $read_timeout  Read timeout (in seconds).
	 * @param int $write_timeout Write timeout (in seconds).
	 *
	 * @return $this
	 */
	public function setTimout($read_timeout = self::DEF_TIMEOUT_READ,
		$write_timeout = self::DEF_TIMEOUT_WRITE)
	{
		$this->checkResource();
		event_buffer_timeout_set(
			$this->resource, $read_timeout, $write_timeout
		);
		$this->read_timeout  = $read_timeout;
		$this->write_timeout = $write_timeout;
		return $this;
	}

	/**
	 * Set the marks for read and write events.
	 *
	 * <p>Libevent does not invoke read callback unless
	 * there is at least <i>lowmark</i> bytes in the
	 * input buffer; if the read buffer is beyond the
	 * <i>highmark</i>, reading is stopped. On output,
	 * the write callback is invoked whenever the
	 * buffered data falls below the <i>lowmark</i>.</p>
	 *
	 * @see event_buffer_watermark_set
	 *
	 * @throws Exception
	 *
	 * @param int $events   Any combination of EV_READ and EV_WRITE.
	 * @param int $lowmark  Low watermark.
	 * @param int $highmark High watermark.
	 *
	 * @return $this
	 */
	public function setWatermark($events, $lowmark = self::DEF_LOWMARK,
		$highmark = self::DEF_HIGHMARK)
	{
		$this->checkResource();
		event_buffer_watermark_set(
			$this->resource, $events, $lowmark, $highmark
		);
		$this->mark_events = $events;
		$this->lowmark     = $lowmark;
		$this->highmark    = $highmark;
		return $this;
	}

	/**
	 * Assign a priority to a buffered event.
	 *
	 * @see event_buffer_priority_set
	 *
	 * @param int $value <p>
	 * Priority level. Cannot be less than zero and
	 * cannot exceed maximum priority level of the
	 * event base (see {@link event_base_priority_init}()).
	 * </p>
	 *
	 * @return $this
	 *
	 * @throws Exception
	 */
	public function setPriority($value = self::DEF_PRIORITY)
	{
		$this->checkResource();
		if (!event_buffer_priority_set($this->resource, $value)) {
			throw new Exception(
				"Can't set buffered event priority to {$value} (event_buffer_priority_set)"
			);
		}
		$this->priority = $value;
		return $this;
	}



	/**
	 * Replaces the current maximum single read size.
	 *
	 * By default, bufferevents won’t read or write the maximum
	 * possible amount of bytes on each invocation of the event
	 * loop; doing so can lead to weird unfair behaviorsand
	 * resource starvation.
	 * On the other hand, the defaults might not be reasonable
	 * for all situations.
	 *
	 * @see event_buffer_max_single_read_set
	 *
	 * @param int $value <p>
	 * Size value in bytes. If it's 0 or above PHP_INT_MAX,
	 * the default libevent value will be used.
	 * </p>
	 *
	 * @return $this
	 *
	 * @throws Exception
	 */
	public function setMaxSingleReadSize($value = self::DEF_READ_SIZE)
	{
		$this->checkResource();
		if (function_exists('event_buffer_max_single_read_set')
		    // Libevent >= 2.1.1-alpha
		    && !event_buffer_max_single_read_set($this->resource, $value)
		) {
			throw new Exception(
				"Can't set maximum single read size to {$value}"
				. " (event_buffer_max_single_read_set)"
			);
		}
		return $this;
	}

	/**
	 * Replaces the current maximum single write size.
	 *
	 * By default, bufferevents won’t read or write the maximum
	 * possible amount of bytes on each invocation of the event
	 * loop; doing so can lead to weird unfair behaviorsand
	 * resource starvation.
	 * On the other hand, the defaults might not be reasonable
	 * for all situations.
	 *
	 * @see event_buffer_max_single_write_set
	 *
	 * @param int $value <p>
	 * Size value in bytes. If it's 0 or above PHP_INT_MAX,
	 * the default libevent value will be used.
	 * </p>
	 *
	 * @return $this
	 *
	 * @throws Exception
	 */
	public function setMaxSingleWriteSize($value = self::DEF_WRITE_SIZE)
	{
		$this->checkResource();
		if (function_exists('event_buffer_max_single_write_set')
		    // Libevent >= 2.1.1-alpha
		    && !event_buffer_max_single_write_set($this->resource, $value)
		) {
			throw new Exception(
				"Can't set maximum single write size to {$value}"
				. " (event_buffer_max_single_write_set)"
			);
		}
		return $this;
	}


	/**
	 * Returns the current maximum single read size.
	 *
	 * @see event_buffer_max_single_read_get
	 *
	 * @return int
	 */
	public function getMaxSingleReadSize()
	{
		$this->checkResource();
		if (function_exists('event_buffer_max_single_read_get')) {
			// Libevent >= 2.1.1-alpha
			return event_buffer_max_single_read_get($this->resource);
		}
		return null;
	}

	/**
	 * Returns the current maximum single write size.
	 *
	 * @see event_buffer_max_single_write_get
	 *
	 * @return int
	 */
	public function getMaxSingleWriteSize()
	{
		$this->checkResource();
		if (function_exists('event_buffer_max_single_write_get')) {
			// Libevent >= 2.1.1-alpha
			return event_buffer_max_single_write_get($this->resource);
		}
		return null;
	}
}
