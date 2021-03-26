<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ArrayAccess;
use Countable;
use Iterator;

class Data implements ArrayAccess, Iterator, Countable
{

  /**
   * @var Array
   */
  protected $data = NULL;

  public function __construct(&$data = array())
  {
    $this->data = &$data;
  }

  public function merge(Data $oData)
  {
    $srcData = (array) $this->getDataCopy();
    $newData = (array) $oData->getDataCopy();
    $this->data = array_replace_recursive($srcData, $newData);
  }

  public function getDataCopy()
  {
    return $this->data;
  }

  protected function &locate_parent(&$offset)
  {
    $offset = explode(':', trim($offset, '[]'));
    if (1 >= count($offset)) {
      return $this->data; // either parent does not exist or we already there
    } else {
      return $this->locate_parent_callback($offset, $this->data); // go deep to locate
    }
  }

  protected function &locate_parent_callback(&$offset, &$data)
  {
    $index = array_shift($offset);
    if (is_array($data) && isset($data[$index])) {
      $data = &$data[$index];
    } else if (is_object($data) && isset($data->{$index})) {
      $data = &$data->{$index};
    } else { // probably parent does not exist at all
      array_unshift($offset, $index);
      return $data;
    }
    if (1 < count($offset)) { // yet we have to go more deep ?
      return $this->locate_parent_callback($offset, $data);
    } else { // it is here! done !!!
      return $data;
    }
  }

  public function offsetExists($offset)
  {
    if (NULL !== $this->offsetGet($offset)) {
      return TRUE;
    }
    return FALSE;
  }

  public function offsetGet($offset)
  {
    $data = &$this->locate_parent($offset);
    $output = NULL;
    if (1 == count($offset)) {
      $index = array_shift($offset);
      if (is_array($data) && isset($data[$index])) {
        $output = &$data[$index];
      } else if (is_object($data)) {
        if (in_array($index, get_object_vars($data))) {
          $output = &$data->{$index}; // only public variable can be used as reference
        } else if (isset($data->{$index})) {
          $output = $data->{$index};
        }
      }
    }
    return $output;
  }

  public function offsetSet($offset, $value)
  {
    $data = &$this->locate_parent($offset);
    while ($index = array_shift($offset)) {
      if (count($offset)) { // if we need to go deep
        $new_value = array();
      } else {
        $new_value = &$value;
      }
      if (is_array($data)) {
        $data[$index] = &$new_value;
        $data = &$data[$index];
      } else if (is_object($data) && isset($data->{$index})) {
        $data->{$index} = null; // required to resolve: Cannot assign by reference to overloaded object
        $data->{$index} = &$new_value;
        $data = &$data->{$index};
      }
    }
  }

  public function offsetUnset($offset)
  {
    $data = &$this->locate_parent($offset);
    if (1 == count($offset)) {
      $index = array_shift($offset);
      if (is_array($data)) {
        unset($data[$index]);
      } else if (is_object($data)) {
        unset($data->{$index});
      }
    }
  }

  public function __isset($name)
  {
    return $this->offsetExists($name);
  }

  public function &__get($name)
  {
    $value = $this->offsetGet($name);
    return $value;
  }

  public function __set($name, $value)
  {
    $this->offsetSet($name, $value);
  }

  public function __unset($name)
  {
    $this->offsetUnset($name);
  }

  public function __toString()
  {
    if (is_object($this->data)) {
      if (method_exists($this->data, '__toString')) {
        return $this->data->__toString();
      }
      return '';
    } else if (!is_array($this->data)) { // arrays are not supported
      return $this->data;
    }
    return '';
  }

  public function current()
  {
    return current($this->data);
  }

  public function key()
  {
    return key($this->data);
  }

  public function next()
  {
    return next($this->data);
  }

  public function rewind()
  {
    return reset($this->data);
  }

  public function valid()
  {
    return $this->offsetExists($this->key());
  }

  public function count()
  {
    return count($this->data);
  }

}