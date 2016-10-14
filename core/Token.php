<?php
/* * ****************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * **************************************************************** */

class Token
{

  /** @const */
  const REPLACE_EMPTY = 0;
  const KEEP_ORIGNAL = 1;

  /** @var array $token */
  public $token = array();

  /** @var integer $default_value */
  public $default_value = Token::KEEP_ORIGNAL;

  public function __construct($token = array())
  {
    $this->token = $token;
  }

  public function add($name, $value = NULL)
  {
    if (is_object($value)) {  // objects
      $this->token[$name] = get_object_vars($value);
    } else {                  // array or simple values
      $this->token[$name] = $value;
    }
  }

  public function merge($oToken)
  {
    $this->token = array_merge_recursive($this->token, $oToken->token);
  }

  public function token_replace($template, $default_value = Token::KEEP_ORIGNAL)
  {
    $this->default_value = $default_value;

    if (!empty($template) && is_array($template)) {
      $result = array();
      foreach ($template as $key => $value) {
        if (is_array($value)) {
          $result[$key] = $this->token_replace($value);
        } else {
          $result[$key] = $this->token_replace_string($value);
        }
      }
      return $result;
    } else {
      return $this->token_replace_string($template);
    }
  }

  public function token_replace_string($in_str)
  {
    // the most outer () is being used to get whole matched string in $macths[1] note matchs[0] have '[]'
    $regex = "/\[(([\w]+)(:[\w]+)*)\]/";
    return preg_replace_callback($regex, array($this, 'token_replace_callback'), $in_str);
  }

  public function token_replace_callback($org_matchs)
  {
    $token = &$this->token;
    $matchs = explode(':', $org_matchs[1]);
    for ($i = 0; $i <= (count($matchs) - 1); $i++) {
      if (is_array($token) && array_key_exists($matchs[$i], $token)) {
        $token = &$token[$matchs[$i]];
      } else if (Token::REPLACE_EMPTY === $this->default_value) {
        return ''; // replace with empty string, to remove it
      } else {
        return $org_matchs[0]; // if nothing above worked then return original
      }
    }
    return $token;
  }

}