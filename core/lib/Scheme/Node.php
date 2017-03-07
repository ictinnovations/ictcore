<?php

namespace ICT\Core\Scheme;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Exception;
use ICT\Core\Action;
use ICT\Core\Application;
use ICT\Core\CoreException;
use ICT\Core\Program;
use ICT\Core\Scheme;

/**
 * Description of Node
 *
 * @author nasir
 */
class Node
{

  /** @var Action $currentAction */
  private $currentAction = array();

  /** @var Scheme $parentScheme  */
  private $parentScheme = null;

  /** @var Scheme $linkedScheme  */
  private $linkedScheme = null;

  /** @var array $condition  */
  private $condition = array();

  /** @var boolean $is_default  */
  public $is_default = false;

  public function __construct($parentScheme, $condition = array())
  {
    $this->parentScheme = $parentScheme;
    $this->condition = $condition;
    try {
      $this->validate_inspect();
      // currently not supported $this->validate_match();
    } catch (Exception $ex) {
      throw new CoreException("500", "Invalid condition", $ex);
    }
  }

  public function compile(Program &$oProgram)
  {
    // don't process if no linkedScheme
    if (empty($this->linkedScheme)) {
      return;
    }
    if (empty($this->currentAction)) {
      $this->currentAction = new Action();
    }
    $linked_application_id = $this->linkedScheme->compile($oProgram);
    $this->currentAction->action = $linked_application_id;
    foreach ($this->condition as $test_field => $test_value) {
      $this->currentAction->data = array($test_field => $test_value);
    }
    // probably parentScheme is already compiled, but it will return the application_id
    $this->currentAction->application_id = $this->parentScheme->compile($oProgram);
    $this->currentAction->is_default = $this->is_default;
    $this->currentAction->save();

    return $this->currentAction->action_id;
  }

  /**
   * Set this node as default, in case all node conditions fialed match
   * @param boolean $is_default
   * @return Node
   */
  public function &set_default($is_default = true) {
    $this->is_default = $is_default;
    return $this;
  }

  /**
   * Attach new application to current scheme and return handle to updated scheme
   * @param Application $oApp
   * @return Scheme
   */
  public function &link(Application $oApp)
  {
    $this->linkedScheme = new Scheme($oApp, $this->parentScheme);
    return $this->linkedScheme;
  }

  private function validate_inspect()
  {
    $oApp = $this->parentScheme->get_application();
    $inspectList = $oApp::$supportedResult;
    foreach (array_keys($this->condition) as $inspect) {
      if (!isset($inspectList[$inspect])) {
        throw new CoreException("500", "Application $oApp->type does not support result type : $inspect");
      }
    }
  }

  private function validate_match()
  {
    $oApp = $this->parentScheme->get_application();
    $resultList = $oApp::$supportedResult;
    foreach (array_keys($this->condition) as $inspect) {
      $matchList = (array)$resultList[$inspect];
      foreach ($matchList as $match) {
        if (!in_array($match, $matchList)) {
          throw new CoreException("500", "Current result does not support this condition : $match");
        }
      }
    }
  }

}