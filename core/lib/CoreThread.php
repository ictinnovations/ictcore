<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

/**
 * Background worker for cron driven task processing.
 *
 * This used to extend Aza\Components\Thread\Thread. aza/thread has no PHP 8
 * release and is unmaintained, so the small part of its surface ICTCore relied
 * on (wait, run, getParam, plus the onFork and onShutdown hooks) is implemented
 * here directly on pcntl.
 *
 * Where pcntl is unavailable, notably under mod_php, work runs inline in the
 * calling process. That matches how ICTCore already behaved: the note in
 * Gateway/Sendmail::send records that multithreading does not work under apache,
 * which is why transmissions are scheduled rather than forked there.
 */
abstract class CoreThread
{

  /** @var array $params arguments passed to the most recent run() */
  private $params = array();

  /**
   * The unit of work. Implemented by each subclass, called once per run().
   */
  abstract protected function process();

  /**
   * Kept so existing callers can stay in the wait()->run() form.
   */
  public function wait()
  {
    return $this;
  }

  /**
   * Runs process() in a forked child when possible, otherwise inline.
   *
   * The child is deliberately not waited on: callers loop over pending tasks and
   * would otherwise serialise them. Children are reaped by ignoring SIGCHLD, so
   * they do not accumulate as zombies for the lifetime of the cron process.
   */
  public function run(...$params)
  {
    $this->params = $params;

    if (!self::can_fork()) {
      $this->run_inline();
      return $this;
    }

    pcntl_signal(SIGCHLD, SIG_IGN);
    $pid = pcntl_fork();

    if ($pid === -1) {
      Corelog::log('fork failed, running ' . static::class . ' inline', Corelog::WARNING);
      $this->run_inline();
    } else if ($pid === 0) {
      // Child. Must never return to the caller's control flow, and must not run
      // the parent's shutdown handlers, hence exit() rather than return.
      $code = 0;
      try {
        $this->onFork();
        $this->process();
        $this->onShutdown();
      } catch (\Throwable $e) {
        Corelog::log('thread ' . static::class . ' failed: ' . $e->getMessage(), Corelog::ERROR);
        $code = 1;
      }
      exit($code);
    }

    return $this;
  }

  public function getParam($index, $default = null)
  {
    return array_key_exists($index, $this->params) ? $this->params[$index] : $default;
  }

  private function run_inline()
  {
    // No fork, so no new database connection and no pid change: calling
    // onFork() here would reconnect the caller's own handle out from under it.
    $this->process();
    $this->onShutdown();
  }

  private static function can_fork()
  {
    return function_exists('pcntl_fork')
        && function_exists('pcntl_signal')
        && PHP_SAPI === 'cli';
  }

  protected function onFork()
  {
    DB::$link = DB::connect(TRUE);
    Corelog::$process_id = getmypid();
    Corelog::log("New thread started for: " . get_class($this), Corelog::FLOW);
  }

  protected function onShutdown()
  {
    // nothing to do
  }

}
