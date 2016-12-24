<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Schedule extends Task
{

  /** @const */
  private static $sub_table = 'schedule';
  private static $sub_fields = array(
      'year',
      'month',
      'day',
      'weekday',
      'hour',
      'minute',
      'task_id',
      'schedule_id'
  );

  /** @var integer */
  public $status = Task::ONHOLD;

  /**
   * @property-read integer $schedule_id 
   * @var integer 
   */
  private $schedule_id = NULL;

  /** @var integer */
  public $year = '*';

  /** @var integer */
  public $month = '*';

  /** @var integer */
  public $day = '*';

  /**
   * @property-write integer $delay
   * @see Schedule::set_delay()
   */
  /**
   * @property-write integer $timestamp
   * @see Schedule::set_timestamp()
   */
  /**
   * @property-write string $datetime
   * @see Schedule::set_datetime()
   */

  /**
   * @property string $weekday
   * @see  Schedule::set_weekday()
   * @var integer 
   */
  private $weekday = '*';

  /** @var integer */
  public $hour = '*';

  /** @var integer */
  public $minute = '*';

  public function load()
  {
    parent::load();

    $this->schedule_id = &$this->task_id;
    $year = $month = $day = $weekday = $hour = $minute = array();

    $query = "SELECT * FROM " . self::$sub_table . " WHERE task_id='%task_id%'";
    $schedule = DB::query(self::$sub_table, $query, array('task_id' => $this->task_id));
    while ($data = mysql_fetch_assoc($schedule)) {
      $year[] = $data['year'];
      $month[] = $data['month'];
      $day[] = $data['day'];
      $weekday[] = $data['weekday'];
      $hour[] = $data['hour'];
      $minute[] = $data['minute'];
    }

    $this->year = $this->merge($year);
    $this->month = $this->merge($month);
    $this->day = $this->merge($day);
    $this->weekday = $this->merge($weekday);
    $this->hour = $this->merge($hour);
    $this->minute = $this->merge($minute);

    Corelog::log("Schedule loaded", Corelog::CRUD);
  }

  public function delete()
  {
    $this->schedule_delete();
    return parent::delete();
  }

  private function schedule_delete()
  {
    Corelog::log("Schedule delete", Corelog::CRUD);
    DB::delete(self::$sub_table, 'task_id', $this->task_id);
  }

  private function split($cron_field, $min = 0, $max = 59)
  {
    $cron_list = array();
    if ($cron_field == '*') {
      $cron_list = array('*');
    } else if (strstr($cron_field, ',')) {
      $temp_list = explode(',', $cron_field);
      foreach ($temp_list as $value) {
        $value = $this->split($value, $min, $max);
        $cron_list = array_merge($cron_list, $value);
      }
    } else if (strstr($cron_field, '-')) {
      list($temp_range_start, $temp_range_end) = explode('-', $cron_field);
      $range_start = $this->_split_field_validate($temp_range_start, $min, $max);
      $range_end = $this->_split_field_validate($temp_range_end, $min, $max);
      $cron_list = range($range_start, $range_end, 1);
    } else if (strstr($cron_field, '/')) {
      list($temp_dividend, $temp_divider) = explode('/', $cron_field);
      $dividend = $this->_split_field_validate($temp_dividend, $min, $max, FALSE);
      $divider = $this->_split_field_validate($temp_divider, $min, $max, FALSE);
      for ($i = $divider; $i <= $dividend; $i = $i + $divider) {
        $cron_list[] = $this->_split_field_validate($i, $min, $max);
      }
    } else {
      $cron_list = array($this->_split_field_validate($cron_field, $min, $max));
    }
    return array_unique($cron_list);
  }

  private function _split_field_validate($input, $min = 0, $max = 59, $check_range = TRUE)
  {
    $max_full = ($min == 0) ? ($max + 1) : $max;
    $input2 = str_replace('*', $max_full, $input);
    $output = preg_replace('[\D]', '', $input2);
    if ($output < $min) {
      $output = abs($output);
    }
    if ($output > $max && $check_range) {
      $output = round(($output % $max_full), 0);
    }
    return $output;
  }

  private function merge($cron_list)
  {
    return implode(',', array_unique($cron_list));
  }

  public function __get($field)
  {
    if (!empty($field) && in_array($field, self::$sub_fields)) {
      return $this->$field;
    }
    return parent::__get($field);
  }

  public function __set($field, $value)
  {
    if (!empty($field) && in_array($field, self::$sub_fields)) {
      $this->$field = $value;
      return;
    }
    parent::__set($field, $value);
  }

  public function set_datetime($date)
  {
    $aDate = date_parse($date);
    if (!empty($aDate['month']) || $aDate['month'] === 0) {
      $this->month = $aDate['month'];
    }
    if (!empty($aDate['day']) || $aDate['day'] === 0) {
      $this->day = $aDate['day'];
      $this->weekday = '*'; // disable weekday based scheduling
    }
    if (!empty($aDate['hour']) || $aDate['hour'] === 0) {
      $this->hour = $aDate['hour'];
    }
    if (!empty($aDate['minute']) || $aDate['minute'] === 0) {
      $this->month = $aDate['minute'];
    }
  }

  public function set_weekday($listWeekday)
  {
    $this->day = '*'; // disable day based scheduling
    $this->weekday = $this->merge($listWeekday);
  }

  public function set_timestamp($timestamp)
  {
    $this->year = gmdate('Y', $timestamp);
    $this->month = gmdate('n', $timestamp);
    $this->day = gmdate('j', $timestamp);
    $this->weekday = '*'; // disable weekday based scheduling
    $this->hour = gmdate('G', $timestamp);
    $this->minute = intval(gmdate('i', $timestamp)); // remove leading zero
  }

  public function set_delay($seconds = 60)
  {
    $cur_time = time();
    return $this->set_timestamp($cur_time + $seconds);
  }

  public function save()
  {
    parent::save();
    $this->schedule_id = &$this->task_id;
    $this->schedule_delete(); // clear all existing schedule entries before saving

    $listYear = $this->split($this->year, 2001, 9999);
    $listMonth = $this->split($this->month, 1, 12);
    $listDay = $this->split($this->day, 1, 31);
    $schedule_count = 0;

    foreach ($listYear as $year) {
      foreach ($listMonth as $month) {
        foreach ($listDay as $day) {
          $schedule_count += $this->schedule_save($year, $month, $day);
        }
      }
    }

    Corelog::log("Schedule created for task : $this->task_id", Corelog::CRUD);
    return $schedule_count;
  }

  private function schedule_save($year, $month, $day)
  {
    $listWeekday = $this->split($this->weekday, 1, 7);
    $listHour = $this->split($this->hour, 0, 23);
    $listMinute = $this->split($this->minute, 0, 59);
    $schedule_count = 0;
    foreach ($listWeekday as $weekday) {
      foreach ($listHour as $hour) {
        foreach ($listMinute as $minute) {
          $data = array(
              'year' => $year,
              'month' => $month,
              'day' => $day,
              'weekday' => $weekday,
              'hour' => $hour,
              'minute' => $minute,
              'task_id' => $this->task_id
          );
          // add new, no authentication needed
          DB::update(self::$sub_table, $data, false);
          $schedule_count++;
        }
      }
    }
    return $schedule_count;
  }

}