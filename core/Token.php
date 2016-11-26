<?php
/* * ****************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * **************************************************************** */

use Twig_Loader_Filesystem;
use Twig_Environment;

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

  public function render_template($template, $default_value = Token::KEEP_ORIGNAL)
  {
    global $path_template; //, $path_cache;

    $this->default_value = $default_value;

    /* ---------------------------------------------- PREPARE TEMPLATE ENGINE */
    $loader = new Twig_Loader_Filesystem($path_template); // template home dir
    $twig = new Twig_Environment($loader, array(
        'autoescape' => false,
            // uncomment following line to enable cache
            // 'cache' => $path_cache,
    ));

    /* ------------------------------------------------------ RENDER TEMPLATE */
    return $twig->render($template, $this->token);
  }

  public function render_variable($variable, $default_value = Token::KEEP_ORIGNAL)
  {
    $this->default_value = $default_value;

    if (!empty($variable) && (is_array($variable) || is_object($variable))) {
      $result = array();
      foreach ($variable as $key => $value) {
        if (is_array($value) || is_object($variable)) {
          $result[$key] = $this->render_variable($value);
        } else {
          $result[$key] = $this->render_string($value);
        }
      }
      return $result;
    } else {
      return $this->render_string($variable);
    }
  }

  public function render_string($in_str)
  {
    // the most outer () is being used to get whole matched string in $macths[1] note matchs[0] have '[]'
    $regex = "/\[(([\w]+)(:[\w]+)*)\]/";
    return preg_replace_callback($regex, array($this, 'render_string_callback'), $in_str);
  }

  public function render_string_callback($org_matchs)
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