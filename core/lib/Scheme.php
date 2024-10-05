<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Scheme\Node;

class Scheme
{

  /** @var Application $currentApplication  */
  private $currentApplication = array();

  /** @var Scheme $parentScheme  */
  private $parentScheme = null;

  /** @var Scheme $linkedScheme  */
  private $linkedScheme = null;

  /** @var Node[] $linkedNodes  */
  private $linkedNodes = array();

  /** @var boolean $compiling */
  public $is_compiling = false;

  public function __construct(Application $oApp, Scheme $parentScheme = null)
  {
    if (!empty($parentScheme)) {
      $this->parentScheme = $parentScheme;
    }
    $this->currentApplication = $oApp;
  }

  public function compile(Program &$oProgram)
  {
    // check if alreay compiled
    if (!empty($this->currentApplication->application_id)) {
      // already compiled, just return the application_id
      return $this->currentApplication->application_id;
    }

    // compilation can be started only from parent scheme
    if (!empty($this->parentScheme) && $this->parentScheme->is_compiling == false) {
      return $this->parentScheme->compile($oProgram);
    } else if (empty($this->parentScheme)) {
      Corelog::log("Compiling porgram", Corelog::LOGIC);
    }

    $this->is_compiling = true;

    // make sure no default value left, so REPLACE_EMPTY in token replacement
    /* $oToken = new Token();
    $oToken->add('program', $oProgram);
    $oToken->add('application', $this->currentApplication);
    $aParameter = $this->currentApplication->parameter_save();
    foreach ($aParameter as $parameter_name => $parameter_value) {
      $this->currentApplication->{$parameter_name} = $oToken->render_variable($parameter_value, Token::KEEP_ORIGNAL);
    } */
    $this->currentApplication->program_id = $oProgram->program_id;
    $this->currentApplication->save();
    $this->currentApplication->deploy($oProgram);

    // Also compile all linked schemes
    if (!empty($this->linkedScheme)) {
      $linked_application_id = $this->linkedScheme->compile($oProgram);
      $tmpAction = new Action();
      $tmpAction->action = $linked_application_id;
      $tmpAction->data = array('result' => 'success');
      $tmpAction->application_id = $this->currentApplication->application_id;
      $tmpAction->is_default = true;
      $tmpAction->save();

    } else if (!empty($this->linkedNodes)) {
      $default_exist = false;
      foreach ($this->linkedNodes as $oNode) {
        if ($oNode->is_default) {
          $default_exist = true;
        }
      }
      foreach ($this->linkedNodes as $oNode) {
        if ($default_exist == false) {
          $oNode->set_default();
          $default_exist = true;
        }
        $oNode->compile($oProgram);
      }
    }

    return $this->currentApplication->application_id;
  }

  /**
   * create new node
   * @return Node a reference to newly created node
   */
  public function &node_create($condition = array())
  {
    // create a new Node
    $oNode = new Node($this, $condition);
    $this->linkedNodes[] = &$oNode;
    return $oNode;
  }

  /**
   * Attach new application to current scheme and return handle to updated scheme
   * @param Application $oApp
   * @return Scheme
   */
  public function &link(Application $oApp)
  {
    $this->linkedScheme = new Scheme($oApp, $this);
    return $this->linkedScheme;
  }

  /**
   * get application associated with this scheme
   * @return Application
   */
  public function get_application() {
    return $this->currentApplication;
  }
}
